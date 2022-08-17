<?php
/**
 * Tambula Simple Redirect Class
 * Christian Berkman 2022
 * 
 * Debug file
 * You may want to keep this away from the public
 */

 use Tambula\Tambula;
 require 'Tambula.php';
 $tambula = new Tambula();

// ---- YOUR CODE BELOW THIS LINE ---- //
$tambula->setRequestUrl('/login');
$tambula->findCountryCode('41.153.64.4');
$tambula->setDefaultRoute('https://default-route.local');
$tambula->loadRoutesFromJson('routes.json');

$tambula->setFilters( $tambula->findLanguageCodes() );

// ---- OUR CODE BELOW THIS LINE ---- //
?>
<html>
    <head>
        <title>Tambula Debug Page</title>
    </head>

    <body>
        <h1>Tambula Debug Page</h1>

        <h2>findRoute()</h2>
        <?php var_dump($tambula->findRoute()); ?>

        <h2>Execution Time</h2>
        <?=$tambula->execTime();?> ms

        <h2>Class Properties</h2>
        <table border="1">
            <tr>
                <td><b>Property</b></td>
                <td><b>Value</b></td>
            </tr>
        <?php 
            $properties = ['defaultRoute', 'requestUrl', 'requestPath', 'requestQuery', 'routes', 'filters', 'languageCodes', 'countryCode', 'geoPLugin'];

            foreach($properties as $property):
        ?>
            <tr>
                <td><?=$property;?></td>
                <td><pre><?php var_dump($tambula->$property);?></pre></td>
            </tr>
        <?php endforeach; ?>
    </table>

    </body>

</html>


