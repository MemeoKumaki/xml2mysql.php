<?php

$dblink = mysqli_connect("localhost","user","password","nombase") or die("Error " . mysqli_error($dblink));


$XmlSource = array (
        'http://monsite.com/flux1.xml'
        ,'http://monsite.com/flux2.xml'
        ,'http://monsite.com/flux3.xml'
);

$XmlSourceSize = sizeof($XmlSource);

for ($index_XmlSource = 0; $index_XmlSource <= $XmlSourceSize-1; $index_XmlSource++) {

        $xmlfileurl     = $XmlSource[$index_XmlSource];

        $XmlSourceNbProcessing = $index_XmlSource+1;
        echo "Téléchargement " .$XmlSourceNbProcessing. " / " .$XmlSourceSize. " : " . $xmlfileurl . "\n\n";

        $xmlfp          = gzopen($xmlfileurl,"r");

        $xmlbuffer = "";
        if ($xmlfp = gzopen($xmlfileurl,"r")) {
                while (!feof($xmlfp)) {
                        $xmlbuffer .= fgets($xmlfp, 4096);
                }
        } else {
                exit;
        }

        $xml            = new SimpleXMLElement($xmlbuffer);

        echo $xmlfileurl . "\n\n";

        if ($argv[1] == "import") {

                $sqlcreatetableproduit = "CREATE TABLE IF NOT EXISTS `Produits` ( `XmlFileUrl` TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                $resultcreatetableproduit = $dblink->query($sqlcreatetableproduit);
        }

        foreach ($xml->xpath('/products/product') as $product) {
                $fields = array_keys((array)($product));
                $data[] = array_values((array)($product));

                if ($argv[1] == "import") {
                        foreach ($product as $key => $val) {

                                echo $key . " : " . $val . "\n" ;

                                $sqlaltertableproduct = "ALTER TABLE `Produits` ADD `" .$key. "` TEXT NOT NULL ;"       ;
                                $resultaltertableproduct = $dblink->query($sqlaltertableproduct)                        ;
                        }

                        foreach ($product as $key => $val) {
                                        $sqlinsertproduct        = "INSERT INTO `table`.`Produits` ("       ;
                                        $toend = count($product);
                                        foreach ($product as $sqlkey => $sqlval) {
                                                $sqlinsertproduct       .= "`" .$sqlkey. "`"                    ;
                                                if (0 != --$toend)
                                                        $sqlinsertproduct .= ","                                ;
                                        }
                                        $sqlinsertproduct       .= ") VALUES ("                                 ;

                                        $toend = count($product);
                                        foreach ($product as $sqlkey => $sqlval) {
                                                $sqlinsertproduct       .= "'" . addslashes(utf8_decode($sqlval)) . "'"                 ;
                                                if (0 != --$toend)
                                                        $sqlinsertproduct .= ","                                ;
                                        }
                                        $sqlinsertproduct       .= ");"                                         ;
                        }
                }
                print "\n SQL: " . $sqlinsertproduct . "\n";
                $resultinsertproduct = $dblink->query($sqlinsertproduct);
        }

        echo "\n\n";
}

?>
