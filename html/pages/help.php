<?php
echo'
<div class="text" align="left">
<p><span class="txttitle">Frequently Asked Questions</span></p>

<blockquote><p>
 1. <a href="#servers">Stat Tracking Servers</a><br>
 2. <a href="#enable">Enabling your personal stats tracking</a><br>
 3. <a href="#info">Information about how the stats work</a><br>
 4. <a href="#types">What are the \'official\' Unreal Tournament gametypes?</a><br>
 5. <a href="#rank">How does ranking work?</a><br>
 6. <a href="#score">Scoring - Individual player points awarded?</a><br>
&nbsp;7. <a href="#tscore">Team Scores?</a><br>
 8. <a href="#glossary">Glossary - Terms and abbreviations.</a><br>
&nbsp;9. <a href="#statserver">Enabling Stats on a Server</a><br>
</p></blockquote>

<p><a name="servers"></a><b>Stat Tracking Servers</b></p>
<blockquote><p>UTStats only does stats tracking for servers that have UTStats
	installed.<br>
	To download and get the latest information, please click the forum link to
	the left.</p></blockquote>

<p><a name="enable"></a><b>Enabling your personal stats tracking</b></p>
<blockquote><p>Currently this is not possible, for now you need to use the same
	nick for this group of servers.<br>
	If the server runs UTGL your stats nick always will be the one you registered
	with so there are no stats tracking issues on UTGL servers.</p></blockquote>

<p><a name="info"></a><b>Information about how the stats work</b></p>
<blockquote><p>A server with stat tracking enabled will send information to the
stats server about the game - each frag, score, etc.  Once the game is
completed the match is processed and posted on the stats pages.  The best
way to look up a recent match you played is either by your stats ID or the
server ID.  You can search for your user ID or the server ID by name.
The user names used on the stats pages are based on the last player name you
used in a match - your stats user name you entered in the networking settings is
not displayed.</p>
<p>Bots are not logged, nor are any kills against them.</p>
</blockquote>

<p><a name="types"></a><b>What are the \'official\' Unreal Tournament gametypes?</b></p>
<blockquote><p>Out of the box Unreal Tournament features the gametypes
Deathmatch, Team Deathmatch, Capture the Flag, Assault and Domination.<br>
There are separate rankings for each of the official game types.</p></blockquote>

<p><a name="rank"></a><b>How does ranking work?</b></p>
<blockquote><p>Ranking points are based on what you do in a game.<br>
Points are awarded for fragging and events (eg. flag captures).<br>
Points are deducted for deaths, suicides and teamkills<br><br>
Player ranking points are reduced until they have completed 300 minutes of games.</p></blockquote>

<p><a name="score"></a><b>Scoring - Individual player points award:</b></p>
<blockquote><p>These depend on the game type you are playing. There are
individual player scores awarded for special achievements, such as captures,
assists, etc. <br>
See the Score tables on the Players or Matches subpages to find
out how many points are actually awarded for each score type.</p></blockquote>

<p><a name="tscore"></a><b>Team Scores?</b></p>
<blockquote><p>Aside from the individual player scoring in team based gametypes
(CTF, AS, DOM), there are also Team Scores, that are awarded to
your team as a whole, for fulfilling a gametype specific objective:<br>
</p><ul>
  <li>Capture the Flag - capturing, covering, killing and returning the flag.</li>
  <li>Assault - achieving an objective.</li>
  <li>Domination - \'holding\' domination points.</li>
</ul>
Note: A team based game is won by the Team Score; the individual player score
sums do not matter!<p></p></blockquote>

<p><br><a name="glossary"></a><span class="txttitle">Glossary</span></p>
<blockquote>

<p><a name="fAbb"></a><b>Abbreviations</b></p>
<blockquote><p>Common abbreviations in UTStats.<br>
- K = Kills, S = Suicides, F = Frags, D = Deaths<br>
- E = Events, TK = Team Kills, TD = Team Deaths<br>
- DM = Deathmatch, TDM = Team Deathmatch, CTF = Capture the Flag,<br>
- AS = Assault, DOM = Domination<br>
- FPH = Frags per Hour, SPH = Score per Hour<br>
- [d] = Time in days, [h] = Time in hours,<br>
- [m],[min] = Time in minutes, [s],[sec] = Time in seconds</p></blockquote>

<p><a name="fDeaths"></a><b>Deaths</b></p>
<blockquote><p>Number of times a player gets killed by another player.<br>
- This does not include environment induced deaths, like trap doors. These and
self kills are counted separately, as suicides.<br>
- Team based deaths are counted as team deaths.<br>
- In tables with weapon specific information, deaths are the number of times a
player died holding that weapon.</p></blockquote>

<p><a name="fDodging"></a><b>Dodging</b></p>
<blockquote><p>Special move in Unreal Tournament, that can be activated by
tapping any movement key twice.  Used by many good players to improve their
maneuverability.</p></blockquote>

<p><a name="fEff"></a><b>Efficiency</b></p>
<blockquote><p>A ratio that denotes the player\'s kill skill by comparing it with
his overall performance.  A perfect efficiency is equal to 1 (100%),
anything less than 0.5 (50%) is below average.<br>
Formula:   Kills / (Kills + Deaths + Suicides [+Team Kills])</p></blockquote>

<p><a name="fEvents"></a><b>Events</b></p>
<blockquote><p>Anything not related to frags, deaths, suicides or kills is
hereby defined as an event. Typical events would be a flag capture (score
related) or a flag drop (not score related). Events are mostly used to track all
the other things going on in a game, that are not frag-related.</p></blockquote>

<p><a name="fFB"></a><b>First Blood</b></p>
<blockquote><p>Special event awarded to the player who gets the first kill in a
newly started match.</p></blockquote>

<p><a name="fFrags"></a><b>Frags</b></p>
<blockquote><p>A player\'s frag count is equal to their kills minus
suicides.  In team games team kills (not team suicides) are also subtracted
from the player\'s kills.</p></blockquote>

<p><a name="fFPH"></a><b>Frags Per Hour</b></p>
<blockquote><p>A ratio between the number of frags a player scores per one
hour.  30 frags in 5 minutes will give you 360 FPH.<br>
Formula: Frags / (Time played in hours)</p></blockquote>

<p><a name="fKills"></a><b>Kills</b></p>
<blockquote><p>Number of times a player kills another player.<br>
Note: UTStats only tracks human vs. human kills. Bot kills and other bot
related events are tracked at the <i>admins discretion</i>.</p></blockquote>

<p><a name="fMK"></a><b>Multi Kills</b></p>
<blockquote><p>Special event awarded to the player for killing other players in
a certain time frame.  Every time a player scores a kill he has up to 3
seconds to make another kill.  So 2 kills in 3 seconds gets you a Double
Kill, 3 kills within 3 seconds apart from another a Multi Kill and so on:<br>
- Double Kill = 2 kills<br>
- Multi Kill = 3 kills<br>
- Ultra Kill = 5 kills<br>
- Monster Kill = 6 kills</p></blockquote>

<p><a name="fPing"></a><b>Ping</b></p>
<blockquote><p>Measure of your connection quality.  Ping is the round trip
delay in milliseconds that your computer has to the game server.  Low
values are not important for a fun game, but it sure helps.</p></blockquote>

<p><a name="fSpree"></a><b>Killing Sprees</b></p>
<blockquote><p>Special event: If you manage to kill 5 or more opponents without
dying yourself, you will be on a killing spree. If you kill more than 10
opponents, you are on a rampage, etc.:<br>
- Killing Spree! 5 kills<br>
- Rampage! 10 kills<br>
- Dominating! 15 kills<br>
- Unstoppable! 20 kills<br>
- God Like! 25 kills</blockquote>

<p><a name="fSuicides"></a><b>Suicides</b></p>
<blockquote><p>Number of times a player dies due to action of their own cause.
Suicides can be environment induced (drowning, getting crushed, falling) or
weapon related (fatal splash damage from their own weapon).</p></blockquote>

<p><a name="fTD"></a><b>Team Deaths</b></p>
<blockquote><p>Number of times a player in a team based game is killed by
someone on their own team.</p></blockquote>

<p><a name="fTK"></a><b>Team Kills</b></p>
<blockquote><p>Number of times a player in a team based game kills someone on
their own team.<br>
Note: Team kills subtract from a player\'s personal frags and thus the team frags
as a whole.</p></blockquote>

<p><a name="fTTL"></a><b>TTL</b></p>
<blockquote><p>TTL is Time to Live.<br>
Its the length of time you are in a game in seconds divided by how many times you die,
thus giving an average time of how long you will live.</p></blockquote>

</blockquote>

<p><a name="statserver"></a><b>Enabling Stats on a Server</b></p>
<blockquote><p>Download and get the latest information on UTStats by clicking
	the forum link to the left.</p></blockquote>

<br>
<a href="#Top">Back to Top</a>
</div>';
?>