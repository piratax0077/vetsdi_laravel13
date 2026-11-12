<?php

namespace App\Services;

use DOMDocument;
use DOMElement;

class SvgDocumentoVeterinario
{
    private const SVG = 'http://www.w3.org/2000/svg';
    private const XLINK = 'http://www.w3.org/1999/xlink';
    private const ELEMENTOS = ['svg', 'g', 'defs', 'title', 'desc', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan', 'linearGradient', 'radialGradient', 'stop', 'clipPath', 'mask', 'symbol', 'use'];
    private const ATRIBUTOS = ['id', 'viewBox', 'preserveAspectRatio', 'version', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'fx', 'fy', 'fr', 'd', 'points', 'transform', 'gradientUnits', 'gradientTransform', 'spreadMethod', 'offset', 'clipPathUnits', 'maskUnits', 'maskContentUnits', 'dx', 'dy', 'rotate', 'textLength', 'lengthAdjust'];
    private const PRESENTACION = ['fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity', 'opacity', 'stop-color', 'stop-opacity', 'clip-path', 'clip-rule', 'mask', 'color', 'font-family', 'font-size', 'font-style', 'font-weight', 'text-anchor', 'dominant-baseline', 'visibility', 'display'];

    public function limpiar(string $bytes): ?string
    {
        $documento = new DOMDocument();
        if (!@$documento->loadXML($bytes, LIBXML_NONET) || $documento->doctype) {
            return null;
        }
        $raiz = $documento->documentElement;
        if ($raiz?->namespaceURI !== self::SVG || $raiz->localName !== 'svg') {
            return null;
        }
        $this->limpiarElemento($raiz);
        return $documento->saveXML($raiz) ?: null;
    }

    private function limpiarElemento(DOMElement $elemento): void
    {
        foreach (iterator_to_array($elemento->attributes) as $atributo) {
            $nombre = $atributo->localName;
            $valor = trim($atributo->value);
            if ($nombre === 'href' && in_array($atributo->namespaceURI, [null, self::XLINK], true)
                && preg_match('/\A#[\w.-]+\z/D', $valor)) {
                continue;
            }
            if ($atributo->namespaceURI === null) {
                if (in_array($nombre, self::ATRIBUTOS, true)
                    || (in_array($nombre, self::PRESENTACION, true) && $this->presentacionValida($valor))) {
                    continue;
                }
                if ($nombre === 'style') {
                    $estilos = [];
                    foreach (explode(';', $valor) as $regla) {
                        [$propiedad, $contenido] = array_map('trim', explode(':', $regla, 2) + ['', '']);
                        if (in_array($propiedad, self::PRESENTACION, true) && $this->presentacionValida($contenido)) {
                            $estilos[] = $propiedad.':'.$contenido;
                        }
                    }
                    $atributo->value = implode(';', $estilos);
                    continue;
                }
            }
            $elemento->removeAttributeNode($atributo);
        }

        foreach (iterator_to_array($elemento->childNodes) as $nodo) {
            if ($nodo instanceof DOMElement && $nodo->namespaceURI === self::SVG && in_array($nodo->localName, self::ELEMENTOS, true)) {
                $this->limpiarElemento($nodo);
            } elseif ($nodo->nodeType !== XML_TEXT_NODE) {
                $elemento->removeChild($nodo);
            }
        }
    }

    private function presentacionValida(string $valor): bool
    {
        return preg_match('/\A(?:[\w .,%#-]+|(?:rgb|rgba|hsl|hsla)\([\d ,.%-]+\)|url\(#[\w.-]+\))\z/iD', $valor) === 1;
    }
}
