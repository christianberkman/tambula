<?php
/**
 * Tambula Simple Redirect Class
 * Christian Berkman 2022
 * 
 * Debug file
 * You may want to keep this away from the public
 */
?>
<html>
    <head>
        <title>Tambula Debug Page</title>
    </head>

    <body>
        <h1>Tambula Debug Page</h1>

        <h2>requestUrl</h2>
        <?=$this->requestUrl;?>

        <h2>findRoute()</h2>
        <?php var_dump($this->findRoute()); ?>

        <h2>Execution Time</h2>
        <?=$this->execTime();?> ms

        <h2>Class Properties</h2>
        <table border="1">
            <tr>
                <td><b>Property</b></td>
                <td><b>Value</b></td>
            </tr>
        <?php 
            $properties = ['defaultRoute', 'requestUrl', 'requestPath', 'requestQuery', 'routes', 'filters', 'languageCodes', 'countryCode', 'geoPlugin'];

            foreach($properties as $property):
        ?>
            <tr>
                <td><?=$property;?></td>
                <td><pre><?php var_dump($this->$property);?></pre></td>
            </tr>
        <?php endforeach; ?>
    </table>

    </body>

</html>


