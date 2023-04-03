<?php
	echo "<style>";
	include ("ExportTypes.css");
	echo "</style>";
	include ("ExportTypes.html");
	echo "<script type='text/javascript'>";
	include("ExportTypes.js");
	echo "</script>";
	
class ExportTypesPlugin extends Omeka_Plugin_AbstractPlugin
{}
