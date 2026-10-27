<?php
// Exportación acotada: nunca modifica tablas de la base de origen.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$pdo=DB::connection()->getPdo();$source=DB::connection()->getDatabaseName();$target='centro_estetica';$dest='D:/laragon/www/centro-estetica';
if(!is_dir($dest.'/vendor'))throw new RuntimeException('Primero copiar el proyecto.');
if(DB::table('information_schema.SCHEMATA')->where('SCHEMA_NAME',$target)->exists())throw new RuntimeException('La base destino ya existe; no se sobrescribe.');
$account='centro_estetica_app';
if($pdo->query("SELECT COUNT(*) FROM mysql.user WHERE User=".$pdo->quote($account))->fetchColumn())throw new RuntimeException('El usuario destino ya existe.');
$pdo->exec("CREATE DATABASE `$target` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$all=['peluquerias','profesionales_estetica','peluqueria_personal','peluqueria_sucursales','peluqueria_procedimientos','peluqueria_bloqueos','peluqueria_clientes','peluqueria_citas','peluqueria_fichas','especies_mascotas','razas_mascotas','tamanos_mascotas','especie_tamano_mascota','regiones','ciudades'];
$empty=['permissions','model_has_permissions','role_has_permissions','verificaciones_correo','password_resets','registro_correo','cache','cache_locks','sessions','jobs','job_batches','failed_jobs'];
$filtered=['users','usuario_roles','roles','model_has_roles','pacientes','mascotas'];
foreach(array_merge($all,$empty,$filtered) as $table)$pdo->exec("CREATE TABLE `$target`.`$table` LIKE `$source`.`$table`");
foreach($all as $table)$pdo->exec("INSERT INTO `$target`.`$table` SELECT * FROM `$source`.`$table`");
$ids=DB::table('peluquerias')->pluck('id_usuario')->merge(DB::table('profesionales_estetica')->pluck('id_usuario'))->merge(DB::table('peluqueria_personal')->pluck('usuario_id'))->filter()->unique()->map(fn($id)=>(int)$id)->implode(',')?:'0';
$pdo->exec("INSERT INTO `$target`.users SELECT * FROM `$source`.users WHERE id IN ($ids)");
$pdo->exec("UPDATE `$target`.users SET remember_token=NULL,two_factor_secret=NULL,two_factor_recovery_codes=NULL,current_team_id=NULL");
$pdo->exec("INSERT INTO `$target`.usuario_roles SELECT * FROM `$source`.usuario_roles WHERE id_usuario IN ($ids) AND tipo IN ('peluqueria','centro_estetico')");
$pdo->exec("INSERT INTO `$target`.roles SELECT * FROM `$source`.roles WHERE name IN ('Peluqueria','CentroEstetico')");
$pdo->exec("INSERT INTO `$target`.model_has_roles SELECT r.* FROM `$source`.model_has_roles r JOIN `$target`.roles t ON t.id=r.role_id JOIN `$target`.users u ON u.id=r.model_id");
$pdo->exec("INSERT INTO `$target`.pacientes SELECT p.* FROM `$source`.pacientes p WHERE EXISTS (SELECT 1 FROM `$target`.peluqueria_clientes c WHERE UPPER(REPLACE(REPLACE(p.rut,'.',''),'-',''))=UPPER(REPLACE(REPLACE(c.rut,'.',''),'-',''))) OR EXISTS (SELECT 1 FROM `$source`.mascotas m JOIN `$target`.peluqueria_clientes c ON c.mascota_id=m.id WHERE m.id_responsable=p.id)");
$pdo->exec("INSERT INTO `$target`.mascotas SELECT m.* FROM `$source`.mascotas m WHERE m.id_responsable IN (SELECT id FROM `$target`.pacientes) OR m.id IN (SELECT mascota_id FROM `$target`.peluqueria_clientes WHERE mascota_id IS NOT NULL)");
$password=bin2hex(random_bytes(24));
foreach(['localhost','127.0.0.1'] as $host){$identity=$pdo->quote($account).'@'.$pdo->quote($host);$pdo->exec('CREATE USER '.$identity.' IDENTIFIED BY '.$pdo->quote($password));$pdo->exec("GRANT ALL PRIVILEGES ON `$target`.* TO $identity");}
$key='base64:'.base64_encode(random_bytes(32));
$env="APP_NAME=\"Centro Estética\"\nAPP_ENV=local\nAPP_KEY=$key\nAPP_DEBUG=false\nAPP_URL=http://localhost/centro-estetica/public\nAPP_LOCALE=es\nAPP_FALLBACK_LOCALE=es\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=$target\nDB_USERNAME=$account\nDB_PASSWORD=$password\nSESSION_DRIVER=file\nSESSION_COOKIE=centro_estetica_session\nSESSION_LIFETIME=120\nCACHE_STORE=file\nCACHE_DRIVER=file\nQUEUE_CONNECTION=sync\nFILESYSTEM_DISK=local\nMAIL_MAILER=log\nMAIL_FROM_ADDRESS=contacto@centro-estetica.test\nMAIL_FROM_NAME=\"Centro Estética\"\nLOG_CHANNEL=stack\nLOG_LEVEL=warning\n";
file_put_contents($dest.'/.env',$env);
$schema="-- Esquema independiente del centro estético. Sin datos personales.\n";
$counts=[];
foreach(array_merge($all,$empty,$filtered) as $table){$create=$pdo->query("SHOW CREATE TABLE `$target`.`$table`")->fetch(PDO::FETCH_NUM)[1];$schema.=$create.";\n\n";$counts[$table]=(int)$pdo->query("SELECT COUNT(*) FROM `$target`.`$table`")->fetchColumn();}
file_put_contents($dest.'/database/schema/centro-estetica.sql',$schema);
$pdo->exec("CREATE TABLE `$target`.migrations (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,migration VARCHAR(255) NOT NULL,batch INT NOT NULL)");
$pdo->exec("INSERT INTO `$target`.migrations (migration,batch) VALUES ('2026_10_25_000000_create_centro_estetica_schema',1)");
// Comprueba el aislamiento con las credenciales reales de la aplicación nueva.
$isolated=new PDO("mysql:host=127.0.0.1;port=3306;dbname=$target;charset=utf8mb4",$account,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$denied=false;try{$isolated->query("SELECT id FROM `$source`.users LIMIT 1");}catch(PDOException $e){$denied=in_array((int)($e->errorInfo[1]??0),[1044,1142],true);}
if(!$denied)throw new RuntimeException('La cuenta nueva no debe acceder a la base veterinaria.');
echo json_encode(['database'=>$target,'tablas'=>$counts,'acceso_base_veterinaria_bloqueado'=>$denied],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
