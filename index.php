<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>

<head>
<title>Two-Pass Assembler</title>
<link rel='shortcut icon' type='image/x-icon' href='/twoPass/favicon.ico' />
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<body>
<div class="page-header">
	<h1>GOD-SSEMBLER | Group 27 two-pass Assembler CSCE 230<small>Aaron Johnson | Johnathan Avery | Andrew Petrzilka</small></h1>
</div>
<div class="container">
<div class="jumbotron">
	<h1>Commands and usage</h1>
	<p><b>Instruction Type bit usage</b></p>
	<p>R type: OpCode (23..20) | Cond (19..16) | S (15) | opx (14..12) | RegD (11..8) | RegS (7..4) | RegT (3..0)</p>
	<p>D Type: OpCode (23..20) | Cond (19..16) | S (15) | Immediate (14..8) | RegS (7..4) | RegT (3..0)</p>
	<p>B Type: OpCode (23..20) | Cond (19..16) | Label (15..0)</p>
	<p>J Type: OpCode (23..20) | Constant (19..0)</p>
	<p><b>Op Codes and OPX</b></p>
	<table>
	<thead>
	R-Type
	</thead>
	<tr>
	<td>
	</td>
	<td width='100px;'><center>OpCode</center></td>
	<td>Opx</td>
	</tr>
	<tr>
		<td>
			Add
		</td>
		<td>
			<center>0000</center>
		</td>
		<td>
			100
		</td>
	</tr>
	<tr>
		<td>
			Sub
		</td>
		<td><center>0000</center></td>
		<td>011</td>
	</tr>
	<tr>
		<td>AND</td>
		<td><center>0000</center></td>
		<td>111</td>
	</tr>
	<tr>
		<td>OR</td>
		<td><center>0000</center></td>
		<td>110</td>
	</tr>
	<tr>
		<td>XOR</td>
		<td><center>0000</center></td>
		<td>101</td>
	</tr>
	<tr>
		<td>sll</td>
		<td><center>0011</center></td>
		<td>000</td>
	</tr>
	<tr>
		<td>cmp</td>
		<td><center>0010</center></td>
		<td>000</td>
	</tr>
	<tr>
		<td>jr</td>
		<td><center>0001</center></td>
		<td>000</td>
	</tr>
	</table>
	<br>
	<table>
	<thead>
	D-Type
	</thead>
	<tr>
	<td>
	</td>
	<td width='100px;'><center>OpCode</center></td>
	</tr>
	<tr>
		<td>lw</td>
		<td><center>0100</center></td>
	</tr>
	<tr>
		<td>sw</td>
		<td><center>0101</center></td>
	</tr>
	<tr>
		<td>addi</td>
		<td><center>0110</center></td>
	</tr>
	<tr>
		<td>si</td>
		<td><center>0111</center></td>
	</tr>
	</table>
	<br>
	<table>
	<thead>
	B-Type
	</thead>
	<tr>
	<td>
	</td>
	<td width='100px;'><center>OpCode</center></td>
	<td>Cond</td>
	</tr>
	<tr>
		<td>b (br)</td>
		<td><center>1000</center></td>
		<td>0000</td>
	</tr>
	<tr>
		<td>bal</td>
		<td><center>1001</center></td>
		<td>0000</td>
	</tr>
	<tr>
		<td>beq</td>
		<td><center>1000</center></td>
		<td>0010</td>
	</tr>
	<tr>
		<td>bne</td>
		<td><center>1000</center></td>
		<td>0011</td>
	</tr>
	<tr>
		<td>blt</td>
		<td><center>1000</center></td>
		<td>1101</td>
	</tr>
	<tr>
		<td>bgt</td>
		<td><center>1000</center></td>
		<td>1100</td>
	</tr>
	<tr>
		<td>ble</td>
		<td><center>1000</center></td>
		<td>1111</td>
	</tr>
	<tr>
		<td>bge</td>
		<td><center>1000</center></td>
		<td>1110</td>
	</tr>
	</table>
	<br/>
	<table>
	<thead>
	J-Type
	</thead>
	<tr>
	<td>
	</td>
	<td width='100px;'><center>OpCode</center></td>
	</tr>
	<tr>
		<td>j</td>
		<td><center>1100</center></td>
	</tr>
	<tr>
		<td>jal</td>
		<td><center>1101</center></td>
	</tr>
	<tr>
		<td>li</td>
		<td><center>1110</center></td>
	</tr>
	</table>
	<h2>Other Import things to note</h2>
	<p>Syntax is fairly import, if wrong syntax is used than PHP will throw notices, please keep the following in mind when using the assembler:</p>
	<ul>
		<li>Make sure there are no additional spaces in commands as this could cause errors in hex values. Example: addi r1 &nbsp;r2 2. There are two spaces in between r1 and r2 which will cause an error with the hex value produced.</li>
		<li>To use a .data section please put it at the end of the code and use some syntax like: Name1 2, 4, 6 which would make a variable with the values 2, 4 and 6.</li>
	</ul>
	<p>I/O devices have saved addresss which can be accessed with the following words</p>
	<ul>
		<li>ss (Slider Switch)</li>
		<li>key (Push Button Keys)</li>
		<li>gled (Green LEDS)</li>
		<li>rled (Red LEDS)</li>
		<li>hex0 (Hex display 0)</li>
		<li>hex1 (Hex Display 1)</li>
		<li>hex2 (Hex Display 2)</li>
		<li>hex3 (Hex Display 3)</li>
		<li>rs232 (Serial Port RS232)</li>
	</ul>
	<p>All I/O names are not case sensitive and example syntax is as follows:</p>
	<ul>
		<li>sw rt gled(rs)</li>
		<li> lw rt ss(rs)</li>
		<li>li r1 key</li>
	</ul>
</div>
<form action="twoPassAssembler.php" method="POST">
<label>Put code below:</label><br/>
<textarea name="code" id="code" cols="100" rows="25"></textarea>
<br/>
<input type="submit" class="btn btn-success" />
</form>
</div>
</body>

</html>