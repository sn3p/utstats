<table class="box" border="0" cellpadding="0" cellspacing="0" width="900">
  <tbody>
    <tr>
      <th class="heading" align="center">Credits</th>
    </tr>
    <tr>
      <td class="credits" align="left">

				<ol>
					<li><a href="#developers">Developers</a></li>
					<li><a href="#changelog">Change Log</a></li>
					<li><a href="#todolist">To Do List</a></li>
					<li><a href="#thanks">Thanks</a></li>
					<li><a href="#copyright">Copyright Notices</a></li>
				</ol>

        <h2><a name="developers"></a>Developers</h2>

				<blockquote>
					<p>
						UTStats was developed by azazel, )&deg;DoE&deg;(-AnthraX, PJMODOS, toa and sn3p.<br>
						The source code is available from <a href="https://github.com/sn3p/utstats" target="_blank">GitHub</a>.<br>
						All original pages are W3C <a href="http://validator.w3.org/check?uri=referer" target="_blank">HTML 4.01</a> and
						<a href="http://jigsaw.w3.org/css-validator/" target="_blank">CSS</a> compliant
					</p>
				</blockquote>

				<h2 class="changelog" style="cursor: pointer;">
					<a name="changelog">Change Log (click to show/hide)</a>
				</h2>

				<div id="contentChangeLog" style="display: none;">
					<blockquote><p>
					<dl>

						<dt>4.4.1 (6/2/2018 by Monk)</dt>
						<dd>
						Added win percentage to career summary.<br>
						<br></dd>

						<dt><strong>4.4.0 (8/1/2018 by HULKSMASH and sn3p)</dt>
						<dd>
						Amazing new theme & sexy styling (by HULKSMASH).<br>
						Implement new design, and many misc fixes.<br>
						Many thanks to HULKSMASH and <a href="http://www.ownedwell.com" target="_blank">OwnedWell</a> !<br>
						</dd><br>

						<dt>4.3.0 (4/1/2018 by killerEye, imported by Monk)</dt>
						<dd>
						Added Graphs of specific stats for CTF, DOM and DM game types.<br>
						<br></dd>

						<dt>4.2.9 (23/7/2011 by sn3p)</dt>
						<dd>
						Fixed MySQL errors and deprecations.<br>
						Fixed sidebar import url.<br>
						Updated README and LICENSE files.<br>
						Cleanup the code.<br>
						<br></dd>

						<dt>beta 4.2.8 (23/7/2011 by Rork)</dt>
						<dd>Bug Fixes:<br>
						Fixed explain rankings (reported by Letylove49)<br>
						Fixed delete player (reported by The Cowboy)<br>
						Fixed delete player from match<br>
						Fixed support for LMS++<br><br></dd>

						<dt>beta 4.2.7 (24/4/2011 by Rork)</dt>
						<dd>Added:<br>
						IP Information in extended player info<br>
						Possible fake nicks in extended player info<br>
						Added support for LMS+<br><br></dd>

						<dd>Bug Fixes:<br>
						Fixed player deletion<br>
						Fixed DOM import with bots<br>
						Fixed player info BT records<br>
						Fixed order players in playersearch<br>
						Fixed player time on server longer then gametime<br>
						Fixed lms player sorting<br>
						Fixed lms ttl<br>
						Fixed SQL table creation (reported by PopCorn)<br><br></dd>

						<dt>beta 4.2.6 (04/12/2010 by Rork)</dt>
						<dd>Bug Fixes:<br>
						Fixed serveral vulnerabilities<br>
						Fixed DOM specific map page not showing<br>
						Fix Assault Match looks<br>
						Add support for new BT times<br>
						Fixed CTF Match Reports<br><br></dd>

						<dt>beta 4.2.5 (23/10/2010 by Rork)</dt>
						<dd>Added:<br>
						Game time in match stats<br>
						Added applicable totals per gametype on the mappage<br>
						Bunny Track stats, <A HREF = "http://www.unrealadmin.org/forums/showthread.php?t=18991" TARGET = "_blank">BT++</A> support only<br>
						Server configuration checker/database setup<br>
						UTDC Screenshots viewer<br>
						ACE Logviewer<br>
						Added out time in LMS<br>
						Added number of players to recent matches<br>
						Hide not applicable statistics from player pages<br>
						Various lay-out improvements<br>
						Filters in maplist<br>
						Show Score under Match Totals for teamgames<br>
						Added debug messages<br><br></dd>

						<dd>Bug Fixes:<br>
						Fixed delete player from match<br>
						Fixed bug where players are spec after merging or teamchange<br>
						Fixed effective rank after merging players<br>
						Dirty fix for divide by zero bug on logimport (was line 47) (gametime = 0)<br>
						Fixed dodgy import log recognition<br>
						Fixed playerlink in explain ranking<br>
						Two bug fixes by Enakin reported in the 4.2.3 release thread<br>
						Fixed LMS ttl by using the time until the player is out.<br>
						Sorted LMS players by ttl, the winner on top<br>
						Fixed php shorttag in import_playerstuff.php<br>
						Prevent removing files from ftp after error<br>
						Prevent importing empty log after ftp error<br>
						Made the main pages W3C HTML 4.01 Compliant<br>
						Fixed some vulnerabilities<br><br></dd>

						<dt>beta 4.2.4 (10/04/2010 by Loki)</dt>
						<dd>Added:<br>
						Breakdown of server occupation per weekday (By Loki)<br>
						Breakdown of country of origin (By Loki)<br>
						Included IpToCountry patch into the release, added GeoIP.dat from March 2010 (By Loki)<br><br></dd>
						<dd>Bug Fixes:<br>
						Fixed PHP opening tags (By Loki)<br><br></dd>

						<dt>beta 4.2.3 (18/01/09 by 2399Skillz)</dt>
						<dd>Bug Fixes:<br>
						Lots of bug fixes/database corrections (By Enakin)<br><br></dd>

						<dt>beta 4.2.2 (30/12/08 by 2399Skillz)</dt>
						<dd>Added:<br>
						Option to import AnthChecker logs (admin viewable only) (Added by Skillz)<br>
						New player merger page for admins(Added by killereye)<br><br></dd>
						<dd>Bug Fixes:<br>
						UT Stats DOM fix (Edited by killereye)<br><br></dd>

						<dt>beta 4.2 (20/10/2005 by Azazel)</dt>
						<dd>Added:<br>
					    Ranking re-calculation option added to admin section<br>
					    Import and display player pings<br>
					    <i>Explain Ranking</i> page<br>
					    Added an option to the admin page to delete temporary tables (if any)<br><br></dd>

						<dd>Bug Fixes:<br>
						Import fix for MySQL v3.x<br>
					    Partial Import delete fix<br>
					    bbcode and Clanbase Report Fix - not showing high score win reports.<br>
					    Ranking fix (was more than 300 mins to get full ranking points)<br>
					    Spectator fix (was logging players as spectators)<br>
					    Efficiency on Players Total worked out as SUM rather than AVG<br>
					   	Clanbase report fixed<br>
					    Totals on Totals Page fixed<br>
					    Events on Totals Page only show if they really happened<br>
					    Number of matches on Totals Page fixed<br>
					    Fixed a crash when trying to import logs that didnt contain IP records<br>
					    Maps sometimes appeared twice on the maps list / incorrect map statistics<br>
					    First/Prev/Next/Last links on map stas page were not working correctly<br>
					    Improved server actor version uploaded (old one may have caused some server lag at times)<br>
					    Updated import.php for new server actor<br>
					    Few actor fixes<br>
					    Weapon stats for second attacking team in assault games were not correct (reported by wgray8231)<br>
					    Delete player from match didn't correctly remove the player from the match (reported by wgray8231)<br><br></dd>

						<dt>beta 4.0</dt>
						<dd>Added:<br>
						Many pages overhauled<br>
						Database overhauled<br>
						Option to import bots or not (off by default)<br>
						Command Line Interface now outputs to text not html<br>
						Ranking stuff on match and player pages include gold/silver/bronze cups for each gametype<br>
						Rankings tweaked so new players get even less points<br>
						Maps page now sortable<br>
						Flag Assists now show, get the new <a href="http://www.unrealadmin.org/forums/showthread.php?t=9561" target="_blank">Smart CTF</a><br>
						Report generator outputting to Clanbase and bbcode format<br>
						Support added for custom weapons and gametypes<br>
						Admin page including server/player merging, deletion of players/matches, renaming of "game types" etc<br>
						Option to compress logs when backing them up (requires bzip/gzip support in php)<br>
						More debugging stuff added<br>
						Accuracy package optimised and recoded for better performance (it will not lag the server in anyway now)<br>
						More detailed weapon statistics added<br>
						Totals page expanded with information like on the old NGStats<br>
						JailBreak should now display its statistics properly<br>
						Purge logs option added<br>
						Graphs now display better regardless of data used<br>
						CTF4 Compatibility<br>
						Date and Game Type filtering on Recent Matches page<br>
						Ability to Ban players<br>
						Ability to ignore matches < X minues in length<br>
						IP Search within Administration<br>
						Ability to ignore matches less than X minutes in length<br>
						Option to import UTDC logs (admin viewable only)<br><br></dd>

						<dd>Bug Fixes:<br>
						Ranking overhauled to better reflect average game play of players<br>
						Cleaned up the importer<br>
						Teamscores now shown correctly regardless of player switching activity<br>
						Kills matrix is now created on combined player records<br>
						Kills against bots no longer counted if bots are not imported<br>
						Domination logs only log when players are in<br>
						Teamkills identified as kills in non-team games (gg Epic :/)<br>
						Eff etc fixed because of above Teamkills bug<br>
						Last line not logging of buffer fixed<br><br></dd>

						<dt>beta 3.2</dt>
						<dd>Added:<br>
						Debugging Option<br>
						Better FTP Capabilities<br>
						Filters carried over on next last etc on player page<br><br></dd>

						<dd>Bug Fixes:<br>
						Imports failing on some versions of php 4.3.x<br>
						Totals page fixed<br>
						Totals info at the top of match pages fixed<br><br></dd>

						<dt>beta 3.1</dt>
						<dd>Added:<br>
						Kills Matchup Matrix
						Country Flags for Players<br>
						Hover Hints over key parts of the page (eg. K F D S)<br>
						Some Graphs<br><br></dd>

						<dd>Bug Fixes:<br>
						Importer can now import unlimited logs<br>
						Kills on match pages not listed<br>
						Games where nothing happens no longer imported<br>
						Players who have 0 kills &amp; 0 deaths no longer get imported<br>
						FTP script re-written<br>
						Pickups removed from insta pages<br>
						Translocator entries removed from logs (throws not kills)<br>
						Multis & Sprees report correct player now<br>
						Kills correctly worked out on non-Team Games<br>
						Frags correctly worked out on all games<br><br></dd>

						<dt>beta 3.0</dt>
						<dd>Added:<br>
						SmartCTF events<br>
						UTGL Compatibility<br><br>
						Updated:<br>
						UTStats actor re-written from scratch, it now uses NGLog files<br>
						Database re-written from scratch<br>
						PHP code re-written from scratch<br><br></dd>

						<dd>Bug Fixes:<br>
						Too many to think about<br><br></dd>

						<dt>beta 2.0</dt>
						<dd>Code rewritten from ground up then lost :(<br><br></dd>

						<dt>beta 1.2</dt>
						<dd>Added:<br>
						Accuracy Code (best in insta but works on all weapons)<br>
						UT2004 spree scheme<br>
						Who killed the Flag Carrier<br>
						<br>
						Updated:<br>
						Complete overhaul of pages/theme to mimic closley UT2004 Stats by Epic<br>
						Cap times added to Clanbase Report<br>
						Stats database, now at least 10-20x smaller<br>
						<br>
						Bug Fixes:<br>
						TeamKills no longer appear in DM<br>
						TeamKills no longer mess up overall stats<br>
						Bot kills etc no longer included in overall stats<br>
						Sprees are unique<br><br></dd>

						<dt>beta 1.1</dt>
						<dd>Added:<br>
						Clanbse Reports for CTF Match's<br>
						30 Recent Match's to Player View<br><br></dd>

						<dt>beta 1</dt>
						<dd>Stats output for:<br>
						Player Joins/Leaves<br>
						Match Start/End<br>
						Frags and Item Pickups<br>
						Sprees (Doubles/Multis and Domination/Monster etc)<br>
						Events</dd>

					</dl></blockquote>
				</div>

				<h2><a name="todolist"></a>To Do List</h2>

				<blockquote><dl><dd>
					<i>"Requests"</i><br>
					Centralise stats<br>
					Web based installer<br>
					Other Anticheat log parser. (UTPure, EUT, etc..)<br>
					Multi language support<br>
					Add sftp support<br>
					Add siege support<br>
					Killgraph as in utstatsdb<br>
					<br></dd></dl>
				</blockquote>

				<h2><a name="thanks"></a>Thanks</h2>

				<blockquote>
				<dl>
				<dd>Epic for making a game that we still play<br>
					kostaki for the database pointers, scoring system and the <a href="http://www.inzane.de/" target="_blank">inzane</a> public servers :)<br>
					Limited for the late night sesions, the linux script and the original zero_out function<br>
					L0cky and Flash for the original FTP Script<br>
					Loph for the 6 different reports o/<br>
					Rush for the improved linux script, testing, suggestions and bug finding<br>
					TNSe for being TNSe<br>
					Truff for testing, suggestions and constant bug finding<br>
					Truff Community for testing, suggestions and input<br>
					UnrealAdmin.org testers and suggesters<br>
					<a href="https://www.passionategaming.net/" target="_blank">PassionateGaming</a> and <a href="http://www.ownedwell.com" target="_blank">OwnedWell</a> for all the pugs and cups!<br>
				</dd></dl></blockquote>

				<h2><a name="copyright"></a>Copyright Notices</h2>

				<blockquote><dl>
				<dd>UTStats<br>
					Copyright (C) 2004/2005 <a href="https://github.com/sn3p/utstats" target="_blank">UTStats</a><br>
					<br>
					This program is free software; you can redistribute and/or modify<br>
					it under the terms of the Open Unreal Mod License.<br>
					<br>
					If you do make any changes, fixes or updates posting them on the<br>
					forum would be appreciated.<br>
					<br>
					UT Query PHP script v1.01 by Almar Joling, 2003<br>
					<a href="http://www.persistentrealities.com/" target="_blank">www.persistentrealities.com</a><br>
					<br>
					pemftp Class by Alexey Dotsenko &lt;alex at paneuromedia dot com&gt;<br>
					<a href="http://www.phpclasses.org/browse/package/1743.html" target="_blank">http://www.phpclasses.org/browse/package/1743.html</a><br>
					<br>
					GeoLite data created by MaxMind<br/>
					Available from <a href="http://www.maxmind.com/app/geolitecountry" target="_blank">http://www.maxmind.com/app/geolitecountry</a><br>
					<br>
					overLIB by Erik Bosrup<br>
					<a href="http://www.bosrup.com/web/overlib/" target="_blank">http://www.bosrup.com/web/overlib/</a><br>
					<br>
					<a href="http://www.highcharts.com/">High Charts</a> - the graphs that made it possible to visualise the data for DOM, DM, CTF.<br>
          <a href="http://iamceege.github.io/tooltipster/">Tooltipster</a> - the tooltips used everywhere except the graphs.<br>
          <a href="http://iconsweets.com/">Yummygum Iconsweets</a> - a few icons came from this awesome set that is free.<br>
          <a href="https://forums.unrealtournament.com/showthread.php?13690-Call-to-Action-Weapon-Icons">Weapon Icons</a> - the weapons icons used are a combination of both DarkAp89 & piemo's designs.<br>

					</dd></dl></blockquote>
				<br>

      </td>
    </tr>
  </tbody>
</table>
