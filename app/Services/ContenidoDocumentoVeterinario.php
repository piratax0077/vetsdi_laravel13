<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class ContenidoDocumentoVeterinario
{
    private const ETIQUETAS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'span', 'font'];

    public function limpiar(string $html): string
    {
        $documento = new DOMDocument();
        $documento->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $cuerpo = $documento->getElementsByTagName('body')->item(0);
        if (!$cuerpo) {
            return '';
        }
        $this->limpiarHijos($cuerpo);
        return implode('', array_map($documento->saveHTML(...), iterator_to_array($cuerpo->childNodes)));
    }

    private function limpiarHijos(DOMNode $padre): void
    {
        foreach (iterator_to_array($padre->childNodes) as $nodo) {
            if ($nodo->nodeType === XML_TEXT_NODE) {
                continue;
            }
            if (!$nodo instanceof DOMElement || in_array($nodo->tagName, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                $padre->removeChild($nodo);
                continue;
            }

            $this->limpiarHijos($nodo);
            $atributos = $this->atributosPermitidos($nodo);
            if (!in_array($nodo->tagName, self::ETIQUETAS, true) || (in_array($nodo->tagName, ['span', 'font'], true) && !$atributos)) {
                // El editor genera div al pulsar Enter. Conserva el salto al retirar la etiqueta.
                if ($nodo->tagName === 'div' && $nodo->previousSibling) {
                    $padre->insertBefore($nodo->ownerDocument->createElement('br'), $nodo);
                }
                while ($nodo->firstChild) {
                    $padre->insertBefore($nodo->firstChild, $nodo);
                }
                $padre->removeChild($nodo);
                continue;
            }

            foreach (iterator_to_array($nodo->attributes) as $atributo) {
                $nodo->removeAttributeNode($atributo);
            }
            foreach ($atributos as $nombre => $valor) {
                $nodo->setAttribute($nombre, $valor);
            }
        }
    }

    private function atributosPermitidos(DOMElement $elemento): array
    {
        if ($elemento->tagName === 'font' && preg_match('/\A[1-7]\z/', $elemento->getAttribute('size'))) {
            return ['size' => $elemento->getAttribute('size')];
        }
        if ($elemento->tagName !== 'span') {
            return [];
        }
        if (in_array($elemento->getAttribute('class'), ['indicacion-dato', 'indicacion-dato-pendiente'], true)
            && preg_match('/\A[a-z][a-z0-9_]{0,63}\z/', $elemento->getAttribute('data-dato'))) {
            return ['class' => 'indicacion-dato', 'data-dato' => $elemento->getAttribute('data-dato')];
        }
        foreach (explode(';', $elemento->getAttribute('style')) as $regla) {
            if (preg_match('/\A\s*color\s*:\s*(#[0-9a-f]{3,8}|[a-z]{1,24}|(?:rgb|rgba|hsl|hsla)\([\d ,.%-]+\))\s*\z/i', $regla, $partes)) {
                return ['style' => 'color:'.$partes[1]];
            }
        }
        return [];
    }
}
