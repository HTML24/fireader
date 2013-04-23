<?php

// FI Card reader

// Opens all files in "fifiles" folder and iterates over them - calling the "parseFile" function with the contents of all files
// Call this function to get started
function init(){
    echo 'Starting FI reader...<br/><br/>';
    $files = scandir("fifiles"); 
    $array = array(); 
    foreach($files as $file)
    {
        if(!is_dir($dir.$file.'/')){

            echo "Iterating over file '" . $file . "'...<br/>";
            $file_contents = file_get_contents ("fifiles/" . $file, 'r');
            parseFile($file_contents);
            echo "<br/>";
            
        }
    }
    
    echo 'FI reader finished.';
}



function parseFile($file_contents)
    {
        echo "Parsing file...<br/><br/>";
        
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $file_contents) as $line)
        {
            if (substr($line, 0, 2) == 'FI') {
                // FI Line
                if($fi_parsed = parseFILine($line))
                {
                    echo "---- New line ----" . "<br/>";
                    echo 'FI number:   ' . $fi_parsed['fi_number'] . "<br/>";
                    echo 'Amount:      ' . $fi_parsed['amount'] . "<br/>";
                    echo 'Payment date:' . $fi_parsed['payment_date'] . "<br/>";
                    echo 'Bank date:   ' . $fi_parsed['payment_date2'] . "<br/>";
                    echo 'Note:        ' . $fi_parsed['note'] . "<br/>";
                    echo "---- Line end ----" . "<br/>";
                    echo '<br/>';

                }
            }

        }
        
    }
    
function parseFILine ($line) {
        $regex = '/^(FI)(030)([0-9]{8})([0-9]{2})([0-9]{19})(.{22})([0-9]{8})([0-9]{15})(.{22})(.{2})([0-9]{9})(.)/m';
        $matches = array();
        if (preg_match($regex, $line, $matches)) {
            if ($matches[12] !== 'N') {
                // Tilbagef¿rsel er ikke N
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