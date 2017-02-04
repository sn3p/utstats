<?php
$hint['F'] = 	array	(	"caption"	=>	"Frags",
								"text" 		=>	"A player's frag count is equal to their kills minus suicides.  In team games team kills (not team suicides) are also subtracted from the player's kills."
							);

$hint['K'] = 	array	(	"caption"	=>	"Kills",
								"text" 		=>	"Number of times a player kills another player.<br>Note: UTStats only tracks human vs. human kills. Bot kills and other bot related events are <em>not</em> tracked."
							);

$hint['D'] = 	array	(	"caption"	=>	"Deaths",
								"text" 		=>	"Number of times a player gets killed by another player.<br>- This does not include environment induced deaths, like trap doors. These and self kills are counted separately, as suicides.<br>- Team based deaths are counted as team deaths."
							);

$hint['S'] = 	array	(	"caption"	=>	"Suicides",
								"text" 		=>	"Number of times a player dies due to action of their own cause. Suicides can be environment induced (drowning, getting crushed, falling) or weapon related (fatal splash damage from their own weapon)."
							);

$hint['TK'] = 	array	(	"caption"	=>	"Team Kills",
								"text" 		=>	"Number of times a player in a team based game kills someone on their own team.<br>Note: Team kills subtract from a player's personal frags and thus the team frags as a whole."
							);

$hint['Multis'] =
					array	(	"caption"	=>	"Multi Kills",
								"text" 		=>	"Special event: If you manage to kill more 2 than people within a short space of time you get a Double Kill, 3 is a Multi Kill etc"
							);

$hint['Sprees'] =
					array	(	"caption"	=>	"Killing Sprees",
								"text" 		=>	"Special event: If you manage to kill 5 or more opponents without dying yourself, you will be on a killing spree. If you kill more than 10 opponents, you are on a rampage, etc."
							);

$hint['EFF'] =	array	(	"caption"	=>	"Efficiency",
								"text" 		=>	"A ratio that denotes the player's kill skill by comparing it with his overall performance.  A perfect efficiency is equal to 1 (100%), anything less than 0.5 (50%) is below average.<br>Formula: Kills / (Kills + Deaths + Suicides [+Team Kills])"
							);

$hint['ACC'] = array	(	"caption"	=>	"Accuracy",
								"text" 		=>	"Overall accuracy when using all weapons.  Most accurate in insta but also very accurate in normal weapons."
							);

$hint['TTL'] = array	(	"caption"	=>	"Time to Live",
								"text" 		=>	"The length of time a player is in a game in seconds divided by how many times he/she dies, thus giving an average time of how long he/she will live."
							);

$hint['DK'] = array	(	"caption"	=>	"Double Kill",
								"text" 		=>	"Killed <strong>2</strong> people in a short space of time without dying himself/herself"
							);

$hint['MK'] = array	(	"caption"	=>	"Multi Kill",
								"text" 		=>	"Killed <strong>3</strong> people in a short space of time without dying himself/herself"
							);

$hint['UK'] = array	(	"caption"	=>	"Ultra Kill",
								"text" 		=>	"Killed <strong>4</strong> people in a short space of time without dying himself/herself"
							);

$hint['MOK'] = array	(	"caption"	=>	"Monster Kill",
								"text" 		=>	"Killed <strong>5</strong> people in a short space of time without dying himself/herself"
							);


$hint['KS'] = array	(	"caption"	=>	"Killing Spree",
								"text" 		=>	"Killed <strong>5</strong> people in a row without dying himself/herself"
							);

$hint['RA'] = array	(	"caption"	=>	"Rampage",
								"text" 		=>	"Killed <strong>10</strong> people in a row without dying himself/herself"
							);

$hint['DO'] = array	(	"caption"	=>	"Dominating",
								"text" 		=>	"Killed <strong>15</strong> people in a row without dying himself/herself"
							);

$hint['US'] = array	(	"caption"	=>	"Unstoppable",
								"text" 		=>	"Killed <strong>20</strong> people in a row without dying himself/herself"
							);

$hint['GL'] = array	(	"caption"	=>	"God Like",
								"text" 		=>	"Killed <strong>25</strong> people in a row without dying himself/herself"
							);

?>