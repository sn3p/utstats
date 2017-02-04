<?php
$matchid = preg_replace('/\D/', '', $_GET[mid]);

echo'<br><table class = "box" border="0" cellpadding="1" cellspacing="2" width="300">
  <tbody>
  <tr>
    <td class="heading" align="center">Match Reports</td>
  </tr>
  <tr>
    <td class="dark" align="center"><a class="darkhuman" href="./?p=report&amp;id='.$matchid.'&amp;rtype=clanbase">Clanbase Cup Format</a></td>
  </tr>
  <tr>
    <td class="dark" align="center"><a class="darkhuman" href="./?p=report&amp;id='.$matchid.'&amp;rtype=bbcode">Forum BBCode Format</a></td>
  </tr>
  </tbody>
</table>';
?>