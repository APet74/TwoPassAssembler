<?php
/*
*****************************************************
****											 ****
****											 ****
****			Working Assembler At:			 ****
****		 petrzilkacoding.com/twoPass		 ****
****				GOD-SSEMBLER				 ****
****											 ****
****											 ****
*****************************************************
*/
error_reporting(E_ALL);
ini_set('display_errors', 'on');
function pregGetNums($value){
	return preg_replace('/[^0-9.]+/', '', $value);
}



/*

R type: OpCode (23..20) | Cond (19..16) | S (15) | opx (14..12) | RegD (11..8) | RegS (7..4) | RegT (3..0)
D Type: OpCode (23..20) | Cond (19..16) | S (15) | Immediate (14..8) | RegS (7..4) | RegT (3..0)
B Type: OpCode (23..20) | Cond (19..16) | Label (15..0)
J Type: OpCode (23..20) | Constant (19..0)

R-Types:
		OpCode	Opx
Add		0000	100
Sub 	0000	011
AND 	0000	111
OR 		0000	110
XOR 	0000	101
sll 	0011	000
cmp 	0010	000
jr 		0001	000

D Types:
		OpCode
lw		0100
sw		0101
addi	0110
si 		0111

B Types:
		OpCode
b 		1000
bal 	1001

J Types:
		OpCode 
j 		1100
jal 	1101
li 		1110
*/



$code = $_POST['code']; // Read in the Code
$counter = 0; //array counter for stored locations of labels
$binary = 0;
$lineNumber = 11;
$staticLineNumber = 12;
$goHereFlag = 0;
$parse = explode("\n", $code); //Parse each line into index of array

$fileFW = fopen("MemoryInitialization.mif", "w") or die("Unable to open file!");
$initialText = "WIDTH=24;\nDEPTH=1024;\n\nADDRESS_RADIX=UNS;\nDATA_RADIX=HEX;\n\nCONTENT BEGIN\n\t0\t:\t000000;\n";
fwrite($fileFW, $initialText);

$addCodes = "\t2\t:\t008000;\n\t3\t:\t004000;\n\t4\t:\t002000;\n\t5\t:\t001000;\n\t6\t:\t003000;\n\t7\t:\t005000;\n\t8\t:\t006000;\n\t9\t:\t007000;\n\t10\t:\t009000;\n\t11\t:\t000000;\n";
//fwrite($fileFW, $AddCodes);
foreach ($parse as $line ){

	$lineNumber++;


	if($goHereFlag == 1) {
		$lineNumber = $staticLineNumber;
		$line = str_replace(',', '', $line);
		$part = explode(' ', $line);
		$array = array('name' => $part[0], 'lineNumber' => $lineNumber);
		$arrayData[$part[0]] = $array;
		if(!isset($part[1]) AND strpos($line, '.') === false){
			$addCodes .= "\t{$lineNumber}\t:\t000000;\n";
			//fwrite($fileFW, $addCodes);

			$lineNumber++;
		}
		$flags = 0;
		foreach($part as $item) {
			if($flags != 0){
			if($item < 16){
				$output = '00000';
				$output .= dechex($item);
			}elseif($item < 256){
				$output = '0000';
				$output .= dechex($item);
			}elseif($item < 4096){
				$output = '000';
				$output .= dechex($item);
			}elseif($item < 65536){
				$output = '00';
				$output .= dechex($item);
			}elseif($item < 1048576){
				$output = '0';
				$output .= dechex($item);
			}else{
				$ouput = dechex($item);
			}
			//echo "<b>" . $item . "</b><br>"; //DEBUG THE SHIT OUT OF DATA SECTION WITH THIS LINE
			$addCodes .= "\t{$lineNumber}\t:\t{$output};\n";
			//fwrite($fileFW, $addCodes);
			$lineNumber++;
			}else{
				$flags++;
			}
		}

		$staticLineNumber = $lineNumber;
		
	}
	if(strpos($line, '.') !== false){
		$goHereFlag =1;
		$lineNumber--;
	}
	if(strpos($line, ':') !== false){ //if the line has a label in it
		$line = str_replace(':', '', $line); //replace the : as we won't want it when doing comparisons
		$line = str_replace(',', '', $line); //normal str replace to replace commands with blank chars 
		$part = explode(' ', $line); //create the array based on spaces being delimeters
		$array = array('label' => $part[0], 'lineNumber' => $lineNumber); //make an associative array to store labels and their location
		$arrayLabel[$part[0]] = $array; //append to the array.
	}
}
$holderStatic = $staticLineNumber;
if($holderStatic < 16){
	$var = '0000';
	$var .= dechex($holderStatic);
}elseif ($holderStatic < 256) {
	$var = '000';
	$var .= dechex($holderStatic);
}elseif($holderStatic < 4096 ){
	$var = '00';
	$var .= dechex($holderStatic);
}elseif($holderStatic < 65536){
	$var = '0';
	$var .= dechex($holderStatic);
}else{
	$var = dechex($holderStatic);
}
$jumpString ="\t1\t:\tc{$var};\n";
fwrite($fileFW, $jumpString);
fwrite($fileFW, $addCodes);

$lineNumber = $staticLineNumber -1;
$ajustedLineNum = $lineNumber - 11;
foreach( $parse as $line ){
	$lineNumber++;
	if(strpos($line, ':') !== false){ //if the line has a label in it
		$line = str_replace(':', '', $line); //make sure : is gone
		$line = str_replace(',', '', $line); //get rid of commas again
		$part = explode(' ', $line); //delimate the array by spaces
		/*
		The following lines move each index down 1 to make the sure the indexs are correct when used later.
		*/
		if(!isset($part[0])){
			$part[0] = 'nop';
			$part[1] = 'nop';
		}
		if(isset($part[1])){
			$part[0] = $part[1];
		} else{
			$part[0] = 'nop';
		}
		if(isset($part[2])){
			$part[1] = $part[2];
		}
		if (isset($part[3])) {
			$part[2] = $part[3];
		}
		if (isset($part[4])) {
			$part[3] = $part[4];
		}
		if (isset($part[5])) {
			$part[4] = $part[5];
		}
		if (isset($part[6])) {
			$part[5] = $part[6];
		}
	}else{ 
		$line = str_replace(',', '', $line);
		$part = explode(" ", $line); //break each line into parts I.E. addi r1 r0 2 --> array[0] => "addi", array[1] => "r1", array[2] => "r0", array[3] => "2"
	}

	/* Flags being set */
	$flag = 0;
	$immedFlag = 0; //if command uses immediate bits 
	$sFlag = 0; //if command uses s bit (that would change)
	$labelFlag = 0; //if command uses label bits
	$constFlag = 0; //if command uses constant bits
	$rType = 0; //if command is R type
	$dType = 0; //if command is D type
	$bType = 0; //if command is B type
	$jType = 0; //if command is J type
	$lineHolder = 0; //initialize lineHolder to 0
	
	if( isset($part[0]) ){
		if( strtolower($part[0]) == "add" ){
			$flag = 2; //2 means first 8 bits are 00 (hex would just ignore it)
			$binary = "000000000100";
			$rType = 1;
		} elseif( strtolower($part[0]) == "sub" ) {
			$flag = 2; //2 means first 8 bits are 00 (hex would just ignore it)
			$binary = "000000000011";
			$rType = 1;
		} elseif( strtolower($part[0]) == "and" ) {
			$flag = 2; //2 means first 8 bits are 00 (hex would just ignore it)
			$binary = "000000000111";
			$rType = 1;
		} elseif( strtolower($part[0]) == "or" ) {
			$flag = 2; //2 means first 8 bits are 00 (hex would just ignore it)
			$binary = "000000000110";
			$rType = 1;
		} elseif( strtolower($part[0]) == "xor" ) {
			$flag = 2; //2 means first 8 bits are 00 (hex would just ignore it)
			$binary = "000000000101";
			$rType = 1;
		} elseif( strtolower($part[0]) == "sll" ) {
			$binary = "001100000000";
			$rType = 1;
		} elseif( strtolower($part[0]) == "cmp" ) {
			$binary = "001000001000";
			$rType = 1;
		} elseif( strtolower($part[0]) == "jr" ) {
			$binary = "000100000000";
			$rType = 1;
		} elseif( strtolower($part[0]) == "lw" ) {
			$immedFlag = 1; //has immediate bits
			$sFlag = 1; //has s bit
			$binary = "01000000";
			$dType = 1;
		} elseif( strtolower($part[0]) == "sw" ) {
			$immedFlag = 1; //has immediate bits
			$sFlag = 1; //has s bit 
			$binary = "01010000";
			$dType = 1;
		} elseif( strtolower($part[0]) == "addi" ) {
			$immedFlag = 1; //has immediate bits
			$sFlag = 1; //has s bit
			$binary = "01100000";
			$dType = 1;
		} elseif( strtolower($part[0]) == "si" ) {
			$immedFlag = 1; //has immediate bits
			$sFlag = 1; //has s bit
			$binary = "01110000";
			$dType = 1;
		} elseif( strtolower($part[0]) == "b" OR strtolower($part[0]) == "br") {
			$labelFlag = 1; //has label bits
			$binary = "10000000";
			$bType = 1;
		} elseif( strtolower($part[0]) == "bal" ) {
			$labelFlag = 1; //has label bits
			$binary = "10010000";
			$bType = 1;
		}elseif( strtolower($part[0]) == "beq" ) {
			$labelFlag = 1; //has label bits
			$binary = "10000010";
			$bType = 1;
		}elseif( strtolower($part[0]) == "bne" ) {
			$labelFlag = 1; //has label bits
			$binary = "10000011";
			$bType = 1;
		}elseif( strtolower($part[0]) == "bgt" ) {
			$labelFlag = 1; //has label bits
			$binary = "10001100";
			$bType = 1;
		}elseif( strtolower($part[0]) == "blt" ) {
			$labelFlag = 1; //has label bits
			$binary = "10001101";
			$bType = 1;
		}elseif( strtolower($part[0]) == "bge" ) {
			$labelFlag = 1; //has label bits
			$binary = "10001110";
			$bType = 1;
		}elseif( strtolower($part[0]) == "ble" ) {
			$labelFlag = 1; //has label bits
			$binary = "10001111";
			$bType = 1;
		} elseif( strtolower($part[0]) == "j" ) {
			$constFlag = 1; //has constant bits
			$binary = "1100";
			$jType = 1;
		} elseif( strtolower($part[0]) == "jal" ) {
			$constFlag = 1; //has constant bits
			$binary = "1101";
			$jType = 1;
		} elseif( strtolower($part[0]) == "li" ) {
			$constFlag = 1; //has constant bits
			$binary = "1110";
			$jType = 1;
		} else {
				$outPut = '000000';
				$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
				fwrite($fileFW, $writeText);
				echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";		
   
		}
	} 

	if ( $rType == 1){
		if( isset($part[1]) ){ //checks to make sure sure instruction is set.
			/*
				The Flags are because the way bindec and dechex work is that it will ignore all leading 0's which we still need to account for.
			*/
			if ($flag == 2){ //checks if the number will have 2 bytes of leading 0's
				$outPut = '00'; //appends two 0's to the final output
			}
			elseif ($flag == 1){ //checks if the number will have 1 byte of leading 0's
				$outPut = 0; //appends one 0 to the final output
			}
			
			if( $flag != 0){ //if flag is not 1 we need outPut to append the binary to the end of the 0's added
				/*
					Will call the function to remove any alpha characters or anything that isn't a number. 
					Then it just convers each instruction part to hex and adds it to the final output string for that instruction
				*/
				$numFromReg = pregGetNums($part[1]); 
				$outPut .= dechex(bindec($binary));
				$outPut .= dechex($numFromReg);
				$outPut .= dechex(pregGetNums($part[2]));
				$outPut .= dechex(pregGetNums($part[3]));
			} else { //otherwise we just start with the first number
				/*
					cmp is slightly different as there are only two registers used and rd is 0000 which is set. Rd is included in $binary for cmp
					Rest of the lines are functionally the same as above, converts to binary->decimal to decimal->hex and assembles output.
				*/
				if(strtolower($part[0]) == 'jr'){
					$outPut = dechex(bindec($binary));
					$numFromReg = pregGetNums($part[1]);
					$outPut .= '0';
					$outPut .= dechex($numFromReg);
					$outPut .= '0';
				}elseif(strtolower($part[0]) != 'cmp'){
					$numFromReg = pregGetNums($part[1]);
					$outPut = dechex(bindec($binary));
					$outPut .= dechex($numFromReg);
					$outPut .= dechex(pregGetNums($part[2]));
					$outPut .= dechex(pregGetNums($part[3]));
				}else{
					$numFromReg = pregGetNums($part[1]);
					$outPut = dechex(bindec($binary));
					$outPut .= 0;
					$outPut .= dechex($numFromReg);
					$outPut .= dechex(pregGetNums($part[2]));
				}
			}
			/*
				These 3 lines appear at the end of each assembly process as they write the output to the file and basic output to the browser which includes what sort of command is executed and the full HEX for that command.
			*/
			$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
			fwrite($fileFW, $writeText);
			echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
		}
	} elseif ($dType == 1) {
		/*
			Addi is the only d type command that doesn't follow the syntax of comm rt, IMM(rs) but instead uses comm rt, rs, IMM and so we have a block set aside 
		*/
		if(strtolower($part[0]) == 'addi'){
			$outPut = dechex(bindec($binary));
			/*
				These lines are used for the immediate, if it's less than 0 or negative we need to flip the 7 bits used and add 1 as well as keep the s bit 0.
			*/
			if($part[3] < 0){
				$part[3] += 128;
			}else{
				$part[3] += 0;
			}
			/*
				if the number in the immediate is less than 16 then we will need to to add a leading 0.
			*/
			if($part[3] < 16){
				$outPut .= 0;
				$outPut .= dechex($part[3]);
			} else {
				$outPut .= dechex($part[3]);
			}
			/*
				Similar code as above in the r type instruction to convert to hex from dec or binary and generate output.
			*/
			$numFromReg2 = pregGetNums($part[2]);
			$outPut .= dechex($numFromReg2);
			$numFromReg1 = pregGetNums($part[1]);
			$outPut .= dechex($numFromReg1);
			$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
			fwrite($fileFW, $writeText);
			echo  $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
		}else{
			/*
				if the instruction is not addi then we need to do the normal conversions and check the immediate bits (for both negative and positive) as well get rid of the parenthesis and any other none numeric values.
				Rest follows same pattern with conversions to hex and outputs.
			*/
			$paraSplit = explode("(", $part[2]);
			$outPut = dechex(bindec($binary));
			if(strtolower($paraSplit[0]) == 'ss'){
				$outPut .= '0';
				$outPut .= dechex(2);
			}elseif(strtolower($paraSplit[0]) == 'key' OR strtolower($paraSplit[0]) == 'keys'){
				$outPut .= '0';
				$outPut .= dechex(3);
			}elseif(strtolower($paraSplit[0]) == 'hex0'){
				$outPut .= '0';
				$outPut .= dechex(4);
			}elseif(strtolower($paraSplit[0]) == 'gled'){
				$outPut .= '0';
				$outPut .= dechex(5);
			}elseif(strtolower($paraSplit[0]) == 'rled'){
				$outPut .= '0';
				$outPut .= dechex(6);
			}elseif(strtolower($paraSplit[0]) == 'hex1'){
				$outPut .= '0';
				$outPut .= dechex(7);
			}elseif(strtolower($paraSplit[0]) == 'hex2'){
				$outPut .= '0';
				$outPut .= dechex(8);
			}elseif(strtolower($paraSplit[0]) == 'hex3'){
				$outPut .= '0';
				$outPut .= dechex(9);
			}elseif(strtolower($paraSplit[0]) == 'rs232'){
				$outPut .= '0';
				$outPut .= decHex(10);
			}else{
				$flagSet = 0;
				if(isset($arrayData)){
					foreach($arrayData as $item){
						if ($item['name'] == $paraSplit[0]){
							if($item['lineNumber'] < 16){
								$outPut .= '0';
							}
							$flagSet = 1;
							$outPut .= dechex($item['lineNumber']);
						}
					}
				}
				if($flagSet == 0){
					if($paraSplit[0] < 0){
						$paraSplit[0] += 128;
					}

					if($paraSplit[0] < 16){
						$outPut .= 0;
						$outPut .= dechex($paraSplit[0]);
					}else{
						$outPut .= dechex($paraSplit[0]);
					}
				}
			}
			$numFromRegs = pregGetNums($paraSplit[1]);
			$outPut .= dechex($numFromRegs);
			$numFromReg1 = pregGetNums($part[1]);
			$outPut .= dechex($numFromReg1);
			$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
			fwrite($fileFW, $writeText);
			echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
		}
	} elseif ($bType == 1) {
		/*
			B type are slightly different as we have labels in the instruction which means we need to use the array of labels and their address we generated up at the top. To do this we use a foreach loop to bust itterate through the loop and check each value in the loop against the supplied value we are looking for. If we find the value we want we need to do a subtraction to find the distance between the label and the instruction branching to that label. If the number if negative we have to subtract 1 to get the correct value. We also have to check and see whether or not he number will need leading 0's and how many. PHP will automatically use 16 byte numbers for negatives and will use least amount of bytes possible for postive so if negative we have to cut off 12 leading F's and leave the remaining 4 bytes to be used.
		*/
		$outPut = dechex(bindec($binary));

		foreach($arrayLabel as $label){
			$labelInst = str_replace("\r", '', $part[1]);
			if(strtolower($label['label']) == strtolower($labelInst)){
				$lineHolder = ($label['lineNumber'] + $ajustedLineNum) - $lineNumber;
				//var_dump($arrayLabel);
				$varHolder = $label['lineNumber'] + $ajustedLineNum;
				//echo $lineHolder . " " . $labelInst . " " . dechex($lineHolder) . " " . $varHolder . " " . $lineNumber . "<br> ";

			}
		}
		if($lineHolder < 0){
			$lineHolder--;
			$lineHolderHex = dechex($lineHolder);
			$lineHolderHex = substr($lineHolderHex, -4);
			$outPut .= $lineHolderHex;
		} else {
			if($lineHolder < 16) {
				$lineHolderAdd = '000';
			}elseif( $lineHolder < 256){
				$lineHolderAdd = '00';
			}elseif($lineholder < 4096){
				$lineHolderAdd = '0';
			}
			$lineHolderHex = dechex($lineHolder);
			$lineHolderAdd .= $lineHolderHex;
			//echo "<b>{$outPut} AND {$lineHolderAdd} AND {$lineHolderHex}</b><br>";
			$outPut .= $lineHolderAdd;
		}
		$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
		fwrite($fileFW, $writeText);
		echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
	} elseif ($jType == 1) {
		/*
			J type is fairly easy as we just convert the opCode from the "table" above to hex and then convert the decimal value of the jump distance to hex.
		*/
		if(strtolower($part[0]) == "li"){
				$outPut = dechex(bindec($binary));
				$part[2] = str_replace("\r", '', $part[2]);
			if(strtolower($part[2]) == 'ss'){
				$outPut .= '000';
				$outPut .= dechex(2);
			}elseif(strtolower($part[2]) == 'key' OR strtolower($part[2]) == 'keys'){
				$outPut .= '000';
				$outPut .= dechex(3);
			}elseif(strtolower($part[2]) == 'hex0'){
				$outPut .= '000';
				$outPut .= dechex(4);
			}elseif(strtolower($part[2]) == 'gled'){
				$outPut .= '000';
				$outPut .= dechex(5);
			}elseif(strtolower($part[2]) == 'rled'){
				$outPut .= '000';
				$outPut .= dechex(6);
			}elseif(strtolower($part[2]) == 'hex1'){
				$outPut .= '000';
				$outPut .= dechex(7);
			}elseif(strtolower($part[2]) == 'hex2'){
				$outPut .= '000';
				$outPut .= dechex(8);
			}elseif(strtolower($part[2]) == 'hex3'){
				$outPut .= '000';
				$outPut .= dechex(9);
			}elseif(strtolower($part[2]) == 'rs232'){
				$outPut .= '000';
				$outPut .= decHex(10);
			}else{
				$flagSet = 0;
				if(isset($arrayData)){
					foreach($arrayData as $item){
						if ($item['name'] == $part[2]){
							if($item['lineNumber'] < 16){
								$outPut .= '000';
							}elseif ($item['lineNumber'] < 256) {
								$outPut .= '00';
							}elseif ($item['lineNumber'] < 4096){
								$outPut .= '0';
							}
							$flagSet = 1;
							$outPut .= dechex($item['lineNumber']);
						}
					}
				}
				if($flagSet == 0){
					if($part[2] < 0){
						$holder = decHex($part[1]);
						$holder = substr($holder, -4);
						$outPut .= $holder;
					}elseif($part[2] < 16 AND $part[2] != 0){
						$outPut .= '000';
					}elseif ($part[2] < 256 AND $part[2] != 0) {
						$outPut .= '00';
					}elseif ($part[2] < 4096 AND $part[2] != 0){
						$outPut .= '0';
					}elseif($part[2] == 0){
						$outPut .= '0000';
					}
					if($part[2] > 0){
						$outPut .= dechex($part[2]);
				}
				}
			}
			$holder = pregGetNums($part[1]);
			$outPut .= dechex($holder);
			$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
			fwrite($fileFW, $writeText);
			echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
		
		}else{
			$outPut = dechex(bindec($binary));
			$part[1] += ($staticLineNumber + 2);
			if($part[1] < 0){
				$holder = decHex($part[1]);
				$holder = substr($holder, -4);
				$outPut .= $holder;
			}elseif($part[1] < 16){
				$outPut .= '0000';
			}elseif ($part[1] < 256) {
				$outPut .= '000';
			}elseif ($part[1] < 4096){
				$outPut .= '00';
			}elseif($part[1] < 65535){
				$outPut .= '0';
			}
			if($part[1] > 0){
				$outPut .= dechex($part[1]);
			}
		
		$writeText = "\t{$lineNumber}\t:\t{$outPut};\n";
		fwrite($fileFW, $writeText);
		echo $lineNumber . " " . $part[0] . " " . $outPut . "<br/>";
	}
	}
}
/*
We increment lineNumber one last time to get us the final lines of the MIF file which initialize the remaining cells to 000000 and add the END; tag.
*/
$lineNumber++;
$endText = "\t[{$lineNumber}..1023]\t:\t000000;\nEND;";
fwrite($fileFW, $endText);

fclose($fileFW);
$randVar = rand();
echo "<a href='http://petrzilkacoding.com/twoPass/MemoryInitialization.mif?VER={$randVar}/'>Download MIF</a><br>";
echo "<a href='http://petrzilkacoding.com/twoPass/index.php'>Go Back</a>";
?>