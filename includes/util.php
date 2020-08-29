<?php
function lightness(string $hex) {
	$r = hexdec($hex[1].$hex[2]);
	$g = hexdec($hex[3].$hex[4]);
	$b = hexdec($hex[5].$hex[6]);
	return (max($r, $g, $b) + min($r, $g, $b)) / 510.0; // HSL algorithm
}
?>