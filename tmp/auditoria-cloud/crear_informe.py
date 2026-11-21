from pathlib import Path
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.text import WD_ALIGN_PARAGRAPH

root=Path(r"D:/laragon/www/vet-sdi_v13")
out=root/"docs/entregables"
out.mkdir(parents=True,exist_ok=True)
doc=Document()
sec=doc.sections[0]
sec.page_width=Inches(8.5);sec.page_height=Inches(11)
sec.top_margin=sec.bottom_margin=Inches(.65)
sec.left_margin=sec.right_margin=Inches(.7)
normal=doc.styles['Normal'];normal.font.name='Calibri';normal.font.size=Pt(11)
normal.paragraph_format.space_after=Pt(7)
normal.paragraph_format.line_spacing=1.08
for name,size in [('Title',25),('Heading 1',17),('Heading 2',12)]:
 st=doc.styles[name];st.font.name='Calibri';st.font.size=Pt(size);st.font.color.rgb=RGBColor(0,0,0)
 st.paragraph_format.space_before=Pt(9);st.paragraph_format.space_after=Pt(7)
footer=sec.footer.paragraphs[0];footer.alignment=WD_ALIGN_PARAGRAPH.RIGHT
r=footer.add_run('Veterchile  |  20 noviembre 2026  |  ');r.font.size=Pt(9)
f=OxmlElement('w:fldSimple');f.set(qn('w:instr'),'PAGE');footer._p.append(f)

def p(t,style=None):
 previous=doc._element.body[-2] if len(doc._element.body)>1 else None
 para=doc.add_paragraph(t,style)
 if previous is not None and previous.tag==qn('w:tbl'):para.paragraph_format.space_before=Pt(7)
 return para
def h(t):doc.add_heading(t,1)
def sub(t):doc.add_heading(t,2)
def bullet(t):p(t,'List Bullet')
def table(headers,rows,widths=None):
 t=doc.add_table(rows=1, cols=len(headers));t.style='Light Shading Accent 1';t.autofit=False
 # Neutral table styling and consistent margins.
 for i,label in enumerate(headers):t.rows[0].cells[i].text=label
 rep=OxmlElement('w:tblHeader');t.rows[0]._tr.get_or_add_trPr().append(rep)
 for row in rows:
  cells=t.add_row().cells
  for i,text in enumerate(row):cells[i].text=text
 for row in t.rows:
  for i,cell in enumerate(row.cells):
   if widths:cell.width=Inches(widths[i])
   for para in cell.paragraphs:
    para.paragraph_format.space_after=Pt(5);para.paragraph_format.space_before=Pt(3)
    for run in para.runs:run.font.size=Pt(10.5)
  pr=row._tr.get_or_add_trPr();pr.append(OxmlElement('w:cantSplit'))
 return t

def page(title):doc.add_page_break();h(title)

p('Revisión de Veterchile y pendientes para publicar en la nube','Title')
p('Informe técnico para administración y desarrollo\n20 de noviembre de 2026')
p('El sistema supera la revisión automática y la apertura de las pantallas principales de los ocho perfiles de cuenta. Se corrigieron fallos reproducibles y se actualizaron dependencias. La recomendación es preparar un entorno de pruebas en la nube antes de habilitar el acceso público: todavía deben cerrarse la migración de datos, las comunicaciones reales, las integraciones externas y la aceptación visual.')
sub('Resultados comprobados')
table(['Comprobación','Resultado'],[
 ('Pruebas PHP aisladas','475 aprobadas y 3141 verificaciones'),
 ('Pruebas JavaScript','104 aprobadas'),
 ('Pantallas principales con cuentas locales','21 respuestas HTTP 200 en ocho perfiles'),
 ('Rutas y sintaxis PHP','2078 rutas con métodos existentes y 1066 archivos sin errores de sintaxis'),
 ('Preparación para producción','Vistas, caché de rutas y recursos de producción compilan'),
 ('Dependencias de producción','Composer sin avisos; npm conserva un aviso bajo en Vue 2'),
], [3.3,3.7])
sub('Correcciones realizadas en esta revisión')
bullet('Videoconsulta: dependencia JWT instalada; fechas de vigencia calculadas desde la consulta; errores de configuración controlados; acceso a llamadas limitado a usuarios autorizados y token de moderador oculto al tutor.')
bullet('Video y almacenamiento: uso de configuración compatible con caché de producción; se respeta FILESYSTEM_DISK y se conecta el token en la vista de llamada.')
bullet('Odontología: el guardado del presupuesto actualiza correctamente la referencia de cambios y evita detectar modificaciones inexistentes.')
bullet('Dependencias: actualización compatible de Laravel, Symfony y otras bibliotecas afectadas; correcciones JavaScript sin migrar Vue ni rediseñar las pantallas.')
p('Las aperturas locales bloquearon escrituras SQL, correo e integraciones HTTP. No se ejecutaron cobros, no se enviaron invitaciones reales y no se publicó el sistema.',None)

page('Revisión por perfil')
p('Las pantallas indicadas se renderizaron con cuentas locales completas. Las operaciones de escritura y los controles de acceso se comprobaron mediante pruebas aisladas. Una respuesta HTTP 200 acredita apertura técnica, no la aceptación visual ni una transacción real con proveedores externos.')
table(['Perfil','Cobertura revisada','Validación pendiente en nube'],[
 ('Tutor','Inicio, perfil y mascotas; permisos, documentos, tutoría compartida y recuperación de cuenta en pruebas.','Invitación y recuperación por correo real; reserva completa desde el sitio informativo.'),
 ('Profesional veterinario','Inicio, perfil, configuración e inventario; agenda, fichas general, cirugía y odontología.','Video con dos participantes; cobro de consumos; revisión móvil de la ficha.'),
 ('Centro veterinario','Inicio, configuración, perfil e inventario; áreas, RRHH, agenda y reportes en pruebas.','Facturas de compra completas; conciliación de inventario, atención y caja.'),
 ('Asistentes','Perfil y pruebas de permisos de agenda, autorizaciones y trabajadores.','Probar cada cargo con permisos reales, incluida enfermería y sus restricciones.'),
 ('Guardería y hotel','Inicio y configuración; solicitudes, capacidades y operación en pruebas.','Reserva, retiro, estadía y entrega completos; avisos reales al tutor.'),
 ('Cementerio de mascotas','Inicio, configuración y lugares; solicitudes y administración en pruebas.','Compra o asignación de lugar, retiro, prestación y comprobante comercial.'),
 ('Peluquería independiente','Inicio y configuración; agenda, fichas, personal, servicios y aislamiento entre negocios.','Reserva a pago con una cuenta real; impresión y revisión móvil.'),
 ('Centro estético','Inicio y configuración; sucursales, personal y separación entre negocios en pruebas.','Operación completa con distintas sucursales y cuentas de personal.'),
], [1.25,2.9,2.85])
p('Hospitalización, laboratorio, imagenología, cirugía y odontología se revisaron como módulos asociados al centro y al profesional. El servicio de hospitalización cuenta con pruebas de equipo, controles y permisos; requiere además aceptación operativa con los cargos reales.',None)

page('Pendientes funcionales antes de abrir al público')
p('P0 significa bloqueo para habilitar el servicio afectado. P1 significa trabajo necesario para cerrar el alcance previsto; un módulo incompleto debe permanecer fuera de la oferta pública hasta su aceptación.')
sub('P0  Videoconsulta con servicio real')
p('Configurar JITSI_APP_ID, JITSI_API_KEY y JITSI_PRIVATE_KEY en el servidor. La firma RSA, la vigencia y los permisos pasan las pruebas; falta una sala real con profesional y tutor, micrófono, cámara, reconexión y acceso por enlace. Responsable: infraestructura y desarrollo. Cierre: llamada real con ambos roles y rechazo de un tercero.')
sub('P0  Correos transaccionales')
p('El correo local usa el controlador log: registra mensajes, pero no los entrega. Configurar proveedor, remitente y autenticación del dominio; verificar que no exista redirección de pruebas. Cierre: recepción real de alta, recuperación de contraseña, invitación a profesional, tutoría compartida y documentos. Responsable: infraestructura y soporte.')
sub('P1  Consumos clínicos y cobro')
p('El descuento de existencias por administración registrada se probó en centro y profesional, con fracciones, material individual y protección contra reenvíos. Falta conectar el importe del consumo con el cobro final de la atención, sin sumarlo dos veces y respetando pagos confirmados. Revisar también vacunas y otras pantallas que registran aplicaciones fuera del flujo de administrados en consulta.')
sub('P1  Documentos de compras del inventario')
p('El ingreso de varios productos por factura y su respaldo están implementados. La pantalla de gestión completa de documentos de compra todavía tiene acciones sin rutas conectadas: guardar documento, cambiar pago, eliminar y exportar. Completar el back end descrito en docs/INSTRUCCIONES_DOCUMENTOS_COMPRA.md, conservando el diseño. Cierre: factura y respaldo vinculados al ingreso, estado de pago y exportación verificables.')
sub('P1  Comercio y sitio informativo')
p('Configurar destinos HTTPS y claves privadas para Veterfood, farmacia, beneficios, contabilidad y API de personas. Probar reserva desde Veterchile informativa hasta el sistema, así como publicación de hotel y cementerio en el comercio real. Las pruebas aisladas no acreditan conexión con esos despliegues externos.')
sub('P1  Mensajería y Vue 2')
p('La recuperación por SMS o WhatsApp requiere proveedor y prueba de entrega; el modo log no envía mensajes. Queda además un aviso de severidad baja en Vue 2.7.16, GHSA-5j4c-8p2g-v4jx. Su corrección requiere una migración mayor de interfaz o una estrategia de soporte; se conserva como pendiente para no alterar el diseño sin validación.')

page('Lista de preparación de la infraestructura')
p('No se ha contratado ni modificado infraestructura de nube. Esta lista debe ejecutarse en staging y repetirse antes de abrir producción.')
table(['Nivel','Acción pendiente y evidencia de cierre'],[
 ('P0','Definir proveedor, dominio y responsables. Servir únicamente public/ con HTTPS y redirección desde HTTP; comprobar certificado y acceso exterior.'),
 ('P0','Preparar el esquema histórico de base de datos. Las migraciones actuales son incrementales y no crean tablas centrales como pacientes, profesionales, mascotas e instituciones. Restaurar una copia controlada y ejecutar las migraciones pendientes en staging.'),
 ('P0','Guardar una copia de base de datos y archivos privados y públicos; realizar una restauración completa de prueba. Registrar quién puede restaurar y cuánto tarda.'),
 ('P0','Crear configuración propia de producción: APP_ENV=production, APP_DEBUG=false, APP_URL HTTPS, clave de aplicación y claves privadas fuera del repositorio. Para datos cifrados existentes, conservar la APP_KEY correspondiente a la copia restaurada.'),
 ('P0','Revisar usuarios de prueba y contraseñas débiles. Deshabilitarlos o rotarlos antes de abrir; no ejecutar CuentasDemoSeeder en producción. Separar cuentas de administración y operación.'),
 ('P0','Proteger sesiones con HTTPS y cookie segura. Definir el dominio de cookie y el tratamiento de proxies del balanceador. Comprobar inicio, cierre y cambio de perfil tras activar la configuración.'),
 ('P0','Mantener la base de datos y phpMyAdmin fuera del acceso público. El compose actual es de desarrollo y expone ambos puertos: no publicarlo sin una configuración específica de producción.'),
 ('P1','Verificar persistencia de storage, permisos de escritura y enlace public/storage. No publicar todo storage/app: ahí residen respaldos de compras y documentos privados.'),
 ('P1','Definir zona horaria y verificar el modelo temporal. El entorno revisado usa UTC; validar visualización y reserva en America/Santiago, incluidos cambios de horario, antes de copiar esa configuración.'),
 ('P1','Definir estrategia de colas y avisos. El entorno actual usa sync y no tiene tareas programadas. Documentar los procesos que requieran recordatorios, habilitar workers o cron si corresponde y supervisar fallos.'),
 ('P1','Configurar alertas de errores, rotación de logs, límites de archivos, retención de respaldos y controles de disponibilidad. Comprobar que los mensajes públicos no expongan SQL, tokens o rutas internas.'),
], [.65,6.35])

page('Pruebas de aceptación en staging')
p('Ejecutar con datos de prueba, cuentas separadas y el mismo dominio, almacenamiento y proveedores que se utilizarán en producción. Cada flujo debe dejar evidencia del resultado, responsable y fecha; un error mantiene abierto el pendiente relacionado.')
sub('Recorridos por usuario')
bullet('Tutor: registrarse, verificar correo, agregar mascota, reservar, confirmar o cancelar, recibir un documento y aceptar una invitación de tutoría. Probar también una cuenta ajena.')
bullet('Profesional: configurar agenda y valor, reservar dentro del turno, intentar una reserva fuera de turno y superpuesta, guardar y finalizar una ficha, consultar historial y confirmar cobro.')
bullet('Centro: crear o vincular personal, asignar permisos, administrar boxes y áreas, registrar una compra de varios productos y comprobar su factura y respaldo.')
bullet('Asistente y enfermería: probar cada permiso con su cuenta. Un cargo sin autorización no debe abrir información clínica ni ejecutar acciones del profesional.')
bullet('Hotel y guardería: reservar cuidado diurno y estadía, comprobar capacidad, registrar retiro a domicilio, informe y entrega. Verificar la vista del tutor.')
bullet('Cementerio: solicitar retiro, reservar o vender un lugar, ejecutar el servicio y comprobar que otro cliente no pueda acceder al registro.')
bullet('Peluquería y centro estético: reservar en sucursales diferentes, comprobar pausas y bloqueos, finalizar atención y verificar historial y saldo.')
sub('Casos transversales obligatorios')
bullet('Inventario dental: consumir 0,125 g de un producto de 10 g y comprobar saldo 9,875 g; repetir el envío sin un segundo descuento. Rechazar media unidad de un insumo individual.')
bullet('Documentos: comprobar acceso por titular, archivos privados, enlaces vencidos, carga de un archivo no permitido y tamaño máximo. Validar recetas, consentimientos e informes PDF.')
bullet('Video y correo: realizar llamada con dos navegadores o dispositivos y recibir correos reales. Las pruebas con servicios simulados no sustituyen este paso.')
bullet('Visual y móvil: revisar headers de cada rol, botones Volver y Cancelar, modales, tablas, campos obligatorios y tamaños pequeños. La auditoría HTTP no constituye una aprobación visual o responsive.')
sub('Condición para abrir al público')
p('Cerrar los P0 del alcance que se va a ofrecer, resolver o excluir de forma controlada los módulos incompletos, aprobar estos recorridos y contar con un respaldo restaurable. Desplegar primero para usuarios de prueba y observar errores antes de ampliar el acceso.')

page('Entrega técnica y orden de publicación')
sub('Archivos principales de esta revisión')
table(['Grupo','Archivos y motivo'],[
 ('Videoconsulta','app/Http/Controllers/JitsiController.php; config/services.php; routes/web.php; .env.example. Firma, configuración y autorización. Las vistas de llamada y EscritorioChofer consumen config para admitir caché.'),
 ('Ficha y almacenamiento','resources/views/general/secciones_ficha/partials/odontograma_mascota.blade.php: referencia de cambios tras guardar. config/filesystems.php: compatibilidad con FILESYSTEM_DISK.'),
 ('Dependencias','composer.json y composer.lock: JWT y actualizaciones compatibles. package-lock.json: correcciones transitivas JavaScript. No se realizó una migración a Vue 3.'),
 ('Pruebas','Nueva VideoconsultaAccesoTest y configuración OpenSSL de prueba; actualización de catálogos, migraciones y campos en las pruebas aisladas. No se eliminaron las validaciones de permisos para hacerlas pasar.'),
], [1.25,5.75])
sub('Secuencia propuesta de despliegue')
for text in [
 'Preparar una versión revisable del código y sus archivos lock; excluir .env, vendor local, node_modules local, sesiones, logs y evidencias con datos de prueba.',
 'Provisionar staging con PHP compatible con composer.lock y MySQL; restaurar la base histórica y los archivos necesarios en volúmenes persistentes.',
 'Instalar dependencias con composer install --no-dev --optimize-autoloader y npm ci; generar los recursos con npm run production. Comprobar extensiones y requisitos de plataforma.',
 'Configurar las variables privadas, correo, video e integraciones. Ejecutar php artisan migrate --force únicamente después del respaldo y sobre el entorno correcto.',
 'Verificar storage, generar cachés de configuración, rutas y vistas y repetir las pruebas de aceptación. No activar caché de configuración antes de completar las variables requeridas.',
 'Respaldar producción, desplegar la versión aprobada y observar errores. Para reversión, conservar la versión anterior y el respaldo compatible; no asumir que revertir el código revierte los datos.',
]:p(text,'List Number')
sub('Evidencias y límites de la revisión')
p('Las evidencias técnicas de esta ejecución están en tmp/auditoria-cloud: resultados PHP y JavaScript, apertura de pantallas, auditoría de rutas, sintaxis, dependencias y compilación. No se incluyen credenciales en este informe. La compilación de producción se comprobó sin sustituir la distribución visual publicada en el proyecto.')
p('La revisión cubre pruebas automatizadas, lectura del código y renderizado HTTP local. Quedan como aceptación de staging las pantallas en navegador y móvil, las transacciones con proveedores reales, la concurrencia y carga esperadas y la recuperación de una copia completa.')

doc.core_properties.title='Revisión de Veterchile y pendientes para publicar en la nube'
doc.core_properties.subject='Revisión por perfil y preparación del despliegue'
doc.core_properties.author='Equipo de desarrollo Veterchile'
for part in [doc.styles.element,doc.element]:
 for border in list(part.xpath('.//w:pBdr')):border.getparent().remove(border)
doc.save(out/'Veterchile_revision_y_pendientes_cloud_2026-11-20.docx')
print(out/'Veterchile_revision_y_pendientes_cloud_2026-11-20.docx')
