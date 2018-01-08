<?php
// Picture configuration file


// General note about colors:
// When specifying a color, you specify it's RGB values in hex (like you do in HTML).
// Example: 00FF00 = green (0 red, 255 green, 0 blue)
//
// One speciality:
// You may also add a fourth 'color', the alpha value.
// It determines the transparency of your color.
// Acceptable values are 00 (completely opaque) to 7F (completely transparent)
// FFFF003F will become half transparent yellow




$i=0;
// Enable users to use this picutre or (temporary) disable it?
$pic[$i]['enabled'] = true;

// Set to true if a gid is required for this image
// (if you're using game related stuff such as rankings or gamename
// or if you want to be the values limited to one game)
$pic[$i]['gidrequired'] = true;

// The template picture where we're going to write stuff at
// We're searching for it in images/templates.
$pic[$i]['load']['template'] = 'basic.png';
// Create a copy of the picture and work with that copy?
// That's needed f.e. if you're using a picture with a palette of colors and want
// to use colors that aren't in the current palette of that picture
$pic[$i]['load']['recreate'] = true;
// If recreating the picture: What shall the background color of the new picture be
// before we copy the template over it?
// (Probably only useful if your template contains transparency)
$pic[$i]['load']['bgcolor'] = 'AAAAAA';
// Do you want to be this background color to become the transparent color?
// (if set to yes, everything painted in this color will be transparent)
$pic[$i]['load']['bgtransparent'] = false;

// What picture type shall we output? (png/jpg/gif)
$pic[$i]['output']['type'] = 'png';

// You may set some defaults here (explanation of the values in the next block)
// If a standard is set, you may omit it in the process blocks -- or use it there to
// override the standard ;)
$pic[$i]['default']['align'] = 'left';
$pic[$i]['default']['angle'] = 0;
$pic[$i]['default']['font'] = 'arbocrest.ttf';
$pic[$i]['default']['fontcolor'] = 'FFFFFF';
$pic[$i]['default']['fontsize'] = 12;



// Now we've loaded the picture and know how to output it.
// Let's place some fancy stuff on it:

// We use 'process' blocks to place things on our image.
// You may use as many process blocks as you like
// Explanation of the possible values:
//
// type - the type of the process block (currently only allowed: text)
//
// The text-block:
// value     - The text to place on the picure
//             inline variables are available (see below)
// align     - The alignment of the text (left, center, right)
//             (optional, default: left)
// fontname  - The name of the TTF file (must be in images/fonts)
//             (optional if you specified a default value)
// fontsize  - The font size. Depending on your version of GD, this should be specified
//             as the pixel size (GD1) or point size (GD2).
//             (optional, default: 12 [or your default value])
// fontcolor - The font color :)
//             (optional, default: FFFFFF [or your default value])
// x_from
// y_from    - The basepoint (roughly the lower-left corner) of the text you want to place
// x_to      - You need to specify this value if you want to alinments center or right
//             we will use an imaginary box, the left corner being x_from and the right corner beiong x_to
//             and then place the text with the alignment you specified into that box
// angle     - The angle in degrees, with 0 degrees being left-to-right reading text. Higher values represent
//             a counter-clockwise rotation. For example, a value of 90 would result in bottom-to-top reading text.
//             Only supported for left aligned text
//             (optional, default: 0 [or your default value])

// It is possible to use inline variables in the 'value' property
// The following texts will be replaced by their appropriate values:
// (333 total ;))
//
//
// %GID%              - The game id
// %PID%              - The player id
// %GAMENAME%         - The gamename (CTF, DM, ...)
// %PLAYERNAME%       - The player's name
// %PLAYERCOUNTRY%    - Two letter ISO-code of the player's country
// %LM_GAMEDATE%      - Date of the last match
//
// %RT%               - Rank text (1st, 2nd, ...)
// %RN%               - Rank number (1, 2, ...)
// %RP%               - Ranking points (1934.34)
//
// The following values are available for different groups of matches:
// %xxx_GAMES%        - number of games in this category
// %xxx_GAMESCORE%
// %xxx_FRAGS%
// %xxx_KILLS%
// %xxx_DEATHS%
// %xxx_SUICIDES%
// %xxx_EFF%          - Efficiency (%)
// %xxx_ACC%          - Accuracy (%)
// %xxx_TTL%          - Time to Live (mm:ss)
// %xxx_GAMETIME%     - Time played (hours as 9,99)
// %xxx_FLAG_CAPTURE% - Flag events:
// %xxx_FLAG_COVER%
// %xxx_FLAG_SEAL%
// %xxx_FLAG_ASSIST%
// %xxx_FLAG_KILL%
// %xxx_FLAG_PICKEDUP%
// %xxx_DOM_CP%       - DOM ControlPoint captures
// %xxx_ASS_OBJ%      - Assault objectives
// %xxx_SPREE_DOUBLE% - xxx kills
// %xxx_SPREE_TRIPLE%
// %xxx_SPREE_MULTI%
// %xxx_SPREE_MEGA%
// %xxx_SPREE_ULTRA%
// %xxx_SPREE_MONSTER%
// %xxx_SPREE_KILL%   - Killing sprees:
// %xxx_SPREE_RAMPAGE%
// %xxx_SPREE_DOM%
// %xxx_SPREE_UNS%
// %xxx_SPREE_GOD%
// %xxx_PU_PADS%      - Pickups
// %xxx_PU_ARMOUR%
// %xxx_PU_KEG%
// %xxx_PU_INVIS%
// %xxx_PU_BELT%
// %xxx_PU_AMP%
// %xxx_RANKMOVEMENT% - Rank movement (+/- 9.99)
//
// replace xxx with:
// LM        for the last match
// WEEK      for all matches of the current week
// LWEEK     for all matches of the last week
// MONTH     for all matches of the current month
// LMONTH    for all matches of the last month
// YEAR      for all matches of the current year
// LYEAR     for all matches of the last year
// TOTAL     for all matches played
// (the above values are always limited to the GID the user specified)
// GTOTAL    for all matches played regardless of GID
//
//
// Examples: '%PLAYERNAME% has played a total of %TOTAL_GAMES% %GAMENAME% games.'
//       ==> 'Player has played a total of 57 Capture the Flag games.'
//
//           '%PLAYERNAME%'s overall accuracy this month was %MONTH_ACC% % while it was %LMONTH_ACC% last month!'
//       ==> 'Player's overall accuracy this month was 42.22 % while it was 27.52 % last month!'


$j=0;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = '%PLAYERNAME%';
$pic[$i]['process'][$j]['fontcolor'] = '000000';
$pic[$i]['process'][$j]['fontsize'] = 18;
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 23;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = '%GAMENAME%';
$pic[$i]['process'][$j]['fontsize'] = 14;
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 43;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = 'Rank:';
$pic[$i]['process'][$j]['fontcolor'] = 'DDDD20';
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 69;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['align'] = 'left';
$pic[$i]['process'][$j]['value'] = '%RT%';
$pic[$i]['process'][$j]['x_from'] = 65;
$pic[$i]['process'][$j]['y_from'] = 69;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = 'Hours:';
$pic[$i]['process'][$j]['fontcolor'] = '000000';
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 91;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['align'] = 'left';
$pic[$i]['process'][$j]['value'] = '%TOTAL_GAMETIME%';
$pic[$i]['process'][$j]['x_from'] = 75;
$pic[$i]['process'][$j]['y_from'] = 91;
$j++;








// The next picture

$i++;
$pic[$i]['enabled'] = false;
$pic[$i]['gidrequired'] = false;
$pic[$i]['load']['template'] = 'basic.png';
$pic[$i]['load']['recreate'] = true;
$pic[$i]['load']['bgcolor'] = 'AAAAAA';
$pic[$i]['load']['bgtransparent'] = false;

$pic[$i]['output']['type'] = 'png';

$pic[$i]['default']['align'] = 'left';
$pic[$i]['default']['angle'] = 0;
$pic[$i]['default']['font'] = 'microsbe.ttf';
$pic[$i]['default']['fontcolor'] = 'FFFFFF';
$pic[$i]['default']['fontsize'] = 10;

$j=0;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = '%PLAYERNAME% sucks!';
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 23;
$j++;
$pic[$i]['process'][$j]['type'] = 'text';
$pic[$i]['process'][$j]['value'] = 'in %GAMENAME% games that is...';
$pic[$i]['process'][$j]['x_from'] = 9;
$pic[$i]['process'][$j]['y_from'] = 43;
$j++;



?>