<?php
$matchid = preg_replace('/\D/', '', $_GET[mid]);

echo'<br><table class = "zebra box" border="0" cellpadding="0" cellspacing="0" width="300">
  <tbody>
  <tr>
    <th class="heading" align="center">Match Report</th>
  </tr>
  <tr>
    <td align="center"><a href="./?p=report&amp;id='.$matchid.'&amp;rtype=bbcode">Forum BBCode Format</a></td>
  </tr>
  </tbody>
</table>';
?>