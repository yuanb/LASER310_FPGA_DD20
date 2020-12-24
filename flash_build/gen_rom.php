<?php
//include_once('view.php');

// 生成 intel hex 文件格式
function bin2ihex($buf)
{
	$l = strlen($buf);

	$s = "";

	$c = 0;
	while($c<$l) {
		if(($c&0xFFFF)==0) {
			$checksum = 2+4;
			$checksum += (($c>>16)&0xFF);
			$checksum += (($c>>24)&0xFF);
			$s .= sprintf(":02000004%04X%02X\n", (($c>>16)&0xFFFF), (~($checksum&0xFF) + 1)&0xFF);
		}

		$n = $l-$c;
		if($n>16) $n=16;
		$checksum = $n;
		$checksum += ($c&0xFF);
		$checksum += (($c>>8)&0xFF);
		$s .= sprintf(":%02X%04X00", $n, ($c&0xFFFF));
		for($i=0;$i<$n;$i++) {
			$ch = ord($buf{$c+$i});
			$s .= sprintf("%02X",$ch);
			$checksum += $ch;
		}

		$s .= sprintf("%02X\n", (~($checksum&0xFF) + 1)&0xFF);

		$c += $n;
	}

	$s .= ":00000001FF\n";

	return $s;
}


function bin2asciihex($buf)
{
	$l = strlen($buf);

	$s = "";

	$c = 0;
	while($c<$l) {
		$n = $l-$c;
		if($n>16) $n=16;

		for($i=0;$i<$n;$i++) {
			$ch = ord($buf{$c+$i});
			$s .= sprintf("%02X",$ch);
		}

		$c += $n;
	}

	return $s;
}


//$bin_name  = strval($argv[1]);
//$file_name  = strval($argv[2]);

$file_name  = "flash_rom.bin";
$hex_file_name  = "flash_rom.hex";

$rom_lst	=	[
	//	bank 0
	//	0000H 16K 0 1 2 3
	[	"vtech/vtechv20.u12",			0*4,	],	// 16K
	[	"vtech/vtechv12.u12",			1*4,	],	// 16K
	[	"vtech/vtechv21.u12",			2*4,	],	// 16K

	//	4000H 8K 4 5
	[	"vtech/vzdosv12.rom",			8*4,	],	// 8K
	[	"vtech/char.rom", 				8*4+2,	],	// 8K

	[	"vtech/vzdosv12_patch.rom",		9*4,	],	// 8K
	[	"vtech/char.rom", 				9*4+2,	],	// 8K

/*
	//	bank 16 ... 31
	//	16K 4 D E F
	[	"autostart/Wordpro.bin",		16*4,	],	// 16K
	[	"autostart/Wordpro.bin",		16*4+1,	],	// 16K
	[	"autostart/invaders.bin", 		17*4,	],	// 16K
	[	"autostart/invaders.bin", 		17*4+1,	],	// 16K

	//	bank 32 ... 63
	//	16K C D E F
	[	"vz/BUST OUT.vz",				32*4,	],	// 16K
	[	"vz/COS-RES.VZ", 				33*4,	],	// 16K
	[	"vz/CRASH.VZ",					34*4,	],	// 16K
	[	"vz/DAWN.VZ", 					35*4,	],	// 16K
	[	"vz/HOPPY.VZ",					36*4,	],	// 16K
	[	"vz/KAMIKAZE.VZ", 				37*4,	],	// 16K
	[	"vz/P-CURSE3.VZ",				38*4,	],	// 16K
	[	"vz/MONITORR.vz", 				39*4,	],	// 16K
	[	"vz/PUCK MAN.vz",				40*4,	],	// 16K
	[	"vz/Space_Ram.vz", 				41*4,	],	// 16K
*/
	//	bank 64 80

	//	bank 96
];

$bin_buf = str_repeat("\x00", 16*1024*16);
//echo strlen($bin_buf);
// 读文件

foreach($rom_lst as $item)
{
	$fn = $item[0];
	$pos = $item[1]*4*1024;

	//echo "in : $fn\n";

	$rom_buf = file_get_contents($fn);
	if($rom_buf===FALSE) exit;
	$rom_len = strlen($rom_buf);

	// 如果后缀是 .vz 需要再开头写入文件长度
	$file_basename = basename($fn);

	$fn_b = basename($fn,".vz");
	if($fn_b != basename($fn))
		$file_basename = $fn_b;

	$fn_b = basename($fn,".VZ");
	if($fn_b != basename($fn))
		$file_basename = $fn_b;


	echo "in : $fn  pos $pos  len $rom_len\n";

	$off=0;

	if( $file_basename != basename($fn) ) {
		echo "VZF fomat $fn\n";
		$bin_buf{$pos+0} = chr($rom_len&0xFF);
		$bin_buf{$pos+1} = chr(($rom_len>>8)&0xFF);
		$off=2;
	}

	for($i=0;$i<$rom_len;$i++) {
		$bin_buf{$i+$off+$pos} = $rom_buf{$i};
	}

	//$n = $pos+2;
	//printf( "%08X %02X", $n, ord($bin_buf{$n}) );
}

echo "out : $file_name\n";

//$n = 0x40002;
//printf( "%08X %02X", $n, ord($bin_buf{$n}) );

file_put_contents($file_name,$bin_buf);

// 生成 intel hex 文件格式

echo "out : $hex_file_name\n";

//file_put_contents($hex_file_name,bin2ihex($bin_buf));
file_put_contents($hex_file_name,bin2asciihex($bin_buf));


