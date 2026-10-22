<?php
chdir(__DIR__.'/..');
foreach (['APP_ENV'=>'testing','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','MAIL_MAILER'=>'array','QUEUE_CONNECTION'=>'sync','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array','CACHE_DRIVER'=>'array','BCRYPT_ROUNDS'=>'4'] as $key=>$value) {
    putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
require 'vendor/autoload.php';
PHPUnit\TextUI\Configuration\Registry::init((new PHPUnit\TextUI\CliArguments\Builder)->fromParameters([]), PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration::create());
require 'tests/CreatesApplication.php'; require 'tests/TestCase.php';
$total=0; $fallas=0; $omitidas=0;
foreach (array_merge(glob('tests/Unit/*Test.php'),glob('tests/Feature/*Test.php')) as $file) {
    require_once $file;
    $class='Tests\\'.basename(dirname($file)).'\\'.basename($file,'.php');
    $n=0; $f=0;
    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->name,'test_')) continue;
        if ($method->getNumberOfRequiredParameters()) { $omitidas++; continue; }
        $total++; $n++;
        try { (new $class($method->name))->runBare(); }
        catch (PHPUnit\Framework\SkippedTest $e) { $omitidas++; echo 'OMITIDA '.$class.'::'.$method->name.PHP_EOL; }
        catch (Throwable $e) { $fallas++; $f++; echo 'FALLO '.$class.'::'.$method->name.' '.get_class($e).': '.substr($e->getMessage(),0,1800).PHP_EOL; }
    }
    echo basename($file).': '.$n.' pruebas, '.$f.' fallas'.PHP_EOL;
}
echo "TOTAL $total; FALLAS $fallas; OMITIDAS $omitidas".PHP_EOL;
exit($fallas ? 1 : 0);
