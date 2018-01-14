<?php

include ("includes/config.php");
include ("includes/functions.php");

if (!isset($_GET['noheader'])) include ("includes/header.php");

switch ($_GET["p"]) {
	case "": page(); break; 			// Our opening page

	case "recent": recent(); break;		// list of recent games, 30 in date order
	case "match": match(); break;		// Single Match stats
	case "matchp": matchp(); break;		// Player stats for single match
	case "report": report(); break;		// Report generator

	case "rank": rank(); break;			// Rankings
	case "ext_rank": ext_rank(); break;	// Extended rankings

	case "servers": servers(); break;	// Server listings
	case "sinfo": sinfo(); break;		// Server info
	case "squery": squery(); break;		// Server query page

	case "players": players(); break;	// Players list
	case "psearch": psearch(); break;	// Player search
	case "pinfo": pinfo(); break;		// Player info
	case "pexplrank": pexplrank(); break;		// Explain ranking

	case "maps": maps(); break;			// Maps list
	case "minfo": minfo(); break;		// Map info

	case "totals": totals(); break;		// Totals summary

	case "watchlist": watchlist(); break;		// The viewer's watchlist

	case "credits": credits(); break;	// Credits
	case "help": help(); break;			// Help Page

	default: page(); break; 			// Our opening page
}

function page() {
	include("pages/home.php");
}

function admin() {
	include("admin.php");
}

function recent() {
	include("pages/recent.php");
}

function match() {
	include("pages/match.php");
}

function matchp() {
	include("pages/match_player.php");
}

function report() {
	include("pages/report.php");
}

function rank() {
	include("pages/rank.php");
}

function ext_rank() {
	include("pages/rank_extended.php");
}

function servers() {
	include("pages/servers.php");
}

function sinfo() {
	include("pages/servers_info.php");
}

function squery() {
	include("pages/servers_query.php");
}

function players() {
	include("pages/players.php");
}

function psearch() {
	include("pages/players_search.php");
}

function pinfo() {
	include("pages/players_info.php");
}

function pexplrank() {
	include("pages/players_explain_ranking.php");
}

function pmatchs() {
	include("pages/players_matchs.php");
}

function pmaps() {
	include("pages/players_maps.php");
}

function maps() {
	include("pages/maps.php");
}

function minfo() {
	include("pages/maps_info.php");
}

function totals() {
	include("pages/totals.php");
}

function watchlist() {
	include("pages/watchlist.php");
}

function credits() {
	include("pages/credits.php");
}

function help() {
	include("pages/help.php");
}

include("includes/footer.php");

?>
