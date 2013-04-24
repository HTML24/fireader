<?php

// FI Card reader

// This particular file simply parses data posted to it. Post data from an HTML file to this one.
function init(){
    parseFile();
}



function parseFile()
    {
        echo "Læser overførsler...<br/><br/>";
        
        $file = $_FILES['file'];
        
        // Loop over all lines in the files
        if ($file['error'] !== 0) {
            return false;
        }

        $fh = fopen($file['tmp_name'], 'r');
        
        if ($fh === false) {
            return false;
        }
        $count = 0;
        $total_sum = 0;
        $payment_dataz = array();
        
        while ($line = fgets($fh)) 
        {
            if (substr($line, 0, 2) == 'FI') {
                // FI Line
                if($fi_parsed = parseFILine($line))
                {

                    echo "Overf�rsel " . (string)($count + 1) . "<br/>";
                    echo 'FI nummer:   ' . '+71<' . $fi_parsed['fi_number'] . "<br/>";
                    echo 'Bel�b:      ' . number_format((double)$fi_parsed['amount'], 2, ',', '.') . " kr.<br/>";
                    echo 'Betalingsdato: ' . $fi_parsed['payment_date'] . "<br/>";
                    echo 'Bank dato:   ' . $fi_parsed['payment_date2'] . "<br/>";
                    echo '<br/>';
                    
                    $total_sum = $total_sum + (double)$fi_parsed['amount'];
                    $count = $count + 1;

                }
            }

        }
        
        echo "Der var i alt " . $count . " overførsler, med et total beløb på " . number_format($total_sum, 2, ',', '.') . " kr. <br/>";
        echo "Husk at tjekke om beløbet stemmer med banken!";
        
    }
    
function parseFILine ($line) {
        $regex = '/^(FI)(030)([0-9]{8})([0-9]{2})([0-9]{19})(.{22})([0-9]{8})([0-9]{15})(.{22})(.{2})([0-9]{9})(.)/m';
        $matches = array();
        if (preg_match($regex, $line, $matches)) {
            if ($matches[12] !== 'N') {
                return false;
            }
            $fi_number = substr($line, 19, 15);
            $paymentdate = substr($matches[3],0, 4) . '-' . substr($matches[3], 4, 2) . '-' . substr($matches[3], 6, 2);
            $paymentdate2 = substr($line,56, 4) . '-' . substr($line, 60, 2) . '-' . substr($line, 62, 2);
            return array('fi_number' => $fi_number, 'amount' => $matches[8] / 100, 'payment_date' => $paymentdate, 'payment_date2' => $paymentdate2, 'note' => $line);
        }

        return false;
    }
    
    
    init();
    
?>
