<?php

// FI Card reader

// Opens all files in "fifiles" folder and iterates over them - calling the "parseFile" function with the contents of all files
// Call this function to get started
function init(){
    parseFile();
}



function parseFile()
    {
        echo "L�ser overf�rsler...<br/><br/>";
        
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
                    echo 'FI nummer:   ' . $fi_parsed['fi_number'] . "<br/>";
                    echo 'Bel�b:      ' . number_format((double)$fi_parsed['amount'], 2, ',', '.') . " kr.<br/>";
                    echo 'Betalingsdato: ' . $fi_parsed['payment_date'] . "<br/>";
                    echo 'Bank dato:   ' . $fi_parsed['payment_date2'] . "<br/>";
                    echo '<br/>';
                    
                    $total_sum = $total_sum + (double)$fi_parsed['amount'];
                    $count = $count + 1;

                }
            }

        }
        
        echo "Der var i alt " . $count . " overf�rsler, med et total bel�b p� " . number_format($total_sum, 2, ',', '.') . " kr. <br/>";
        echo "Husk at tjekke om bel�bet stemmer med banken!";
        
    }
    
function parseFILine ($line) {
        $regex = '/^(FI)(030)([0-9]{8})([0-9]{2})([0-9]{19})(.{22})([0-9]{8})([0-9]{15})(.{22})(.{2})([0-9]{9})(.)/m';
        $matches = array();
        if (preg_match($regex, $line, $matches)) {
            if ($matches[12] !== 'N') {
                // Tilbagef�rsel er ikke N
                return false;
            }
            $fi_number = substr($matches[5], 0, -1);
            $paymentdate = substr($matches[3],0, 4) . '-' . substr($matches[3], 4, 2) . '-' . substr($matches[3], 6, 2);
            $paymentdate2 = substr($line,56, 4) . '-' . substr($line, 60, 2) . '-' . substr($line, 62, 2);
            return array('fi_number' => (int)$fi_number, 'amount' => $matches[8] / 100, 'payment_date' => $paymentdate, 'payment_date2' => $paymentdate2, 'note' => $line);
        }

        return false;
    }
    
    
    init();
    
?>