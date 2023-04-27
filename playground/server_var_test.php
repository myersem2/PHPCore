<?php

if($_SERVER['REMOTE_ADDR'] !== '172.12.152.106') die('NOT AUTHORIZED');

$vars = [
'php' => ['SHELL','PWD','LOGNAME','XDG_SESSION_TYPE','MOTD_SHOWN','HOME','LANG','LS_COLORS','SSH_CONNECTION','LESSCLOSE','XDG_SESSION_CLASS','TERM','LESSOPEN','USER','SHLVL','XDG_SESSION_ID','XDG_RUNTIME_DIR','SSH_CLIENT','XDG_DATA_DIRS','PATH','DBUS_SESSION_BUS_ADDRESS','SSH_TTY','OLDPWD','_','PHP_SELF','SCRIPT_NAME','SCRIPT_FILENAME','PATH_TRANSLATED','DOCUMENT_ROOT','REQUEST_TIME_FLOAT','REQUEST_TIME','argv','argc'],
'cli' => ['SHELL','PWD','LOGNAME','XDG_SESSION_TYPE','MOTD_SHOWN','HOME','LANG','LS_COLORS','SSH_CONNECTION','LESSCLOSE','XDG_SESSION_CLASS','TERM','LESSOPEN','USER','SHLVL','XDG_SESSION_ID','XDG_RUNTIME_DIR','SSH_CLIENT','XDG_DATA_DIRS','PATH','DBUS_SESSION_BUS_ADDRESS','SSH_TTY','OLDPWD','_','PHP_SELF','SCRIPT_NAME','SCRIPT_FILENAME','PATH_TRANSLATED','DOCUMENT_ROOT','REQUEST_TIME_FLOAT','REQUEST_TIME','argv','argc'],
'apc' => ['PHPCORE_BOOTSTRAP','HTTP_HOST','HTTP_CONNECTION','HTTP_UPGRADE_INSECURE_REQUESTS','HTTP_USER_AGENT','HTTP_ACCEPT','HTTP_ACCEPT_LANGUAGE','HTTP_COOKIE','HTTP_ACCEPT_ENCODING','PATH','SERVER_SIGNATURE','SERVER_SOFTWARE','SERVER_NAME','SERVER_ADDR','SERVER_PORT','REMOTE_ADDR','DOCUMENT_ROOT','REQUEST_SCHEME','CONTEXT_PREFIX','CONTEXT_DOCUMENT_ROOT','SERVER_ADMIN','SCRIPT_FILENAME','REMOTE_PORT','GATEWAY_INTERFACE','SERVER_PROTOCOL','REQUEST_METHOD','QUERY_STRING','REQUEST_URI','SCRIPT_NAME','PHP_SELF','REQUEST_TIME_FLOAT','REQUEST_TIME'],
];

$example = [
  'PHPCORE_BOOTSTRAP' => '/var/www/phpcore/src/bootstrap.php',
  'HTTP_HOST' => 'dev.warsupremacy.com',
  'HTTP_CONNECTION' => 'keep-alive',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_COOKIE' => 'WS-SessionID=4v2i22fgno1vr9todljptgc0cp',
  'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin',
  'SERVER_SIGNATURE' => '<address>Apache/2.4.52 (Ubuntu) Server at dev.warsupremacy.com Port 80</address>',
  'SERVER_SOFTWARE' => 'Apache/2.4.52 (Ubuntu)',
  'SERVER_NAME' => 'dev.warsupremacy.com',
  'SERVER_ADDR' => '10.0.0.10',
  'SERVER_PORT' => '80',
  'REMOTE_ADDR' => '172.12.152.106',
  'DOCUMENT_ROOT' => '/var/www/html/',
  'REQUEST_SCHEME' => 'http',
  'CONTEXT_PREFIX' => '',
  'CONTEXT_DOCUMENT_ROOT' => '/var/www/html/',
  'SERVER_ADMIN' => 'webmaster@WarSupremacy.com',
  'SCRIPT_FILENAME' => '/var/www/html/playground/test.php',
  'REMOTE_PORT' => '59494',
  'GATEWAY_INTERFACE' => 'CGI/1.1',
  'SERVER_PROTOCOL' => 'HTTP/1.1',
  'REQUEST_METHOD' => 'GET',
  'QUERY_STRING' => '',
  'REQUEST_URI' => '/playground/test.php',
  'SCRIPT_NAME' => '/playground/test.php',
  'PHP_SELF' => '/playground/test.php',
  'REQUEST_TIME_FLOAT' => 1682458345.1759,
  'REQUEST_TIME' => 1682458345,
  'SHELL' => '/bin/bash',
  'PWD' => '/var/www/phpcore/dev',
  'LOGNAME' => 'ubuntu',
  'XDG_SESSION_TYPE' => 'tty',
  'MOTD_SHOWN' => 'pam',
  'HOME' => '/home/ubuntu',
  'LANG' => 'C.UTF-8',
  'LS_COLORS' => 'rs=0:di=01;34:ln=01;36:mh=00:pi=40;33:so=01;35:do=01;35:bd=40;33;01:cd=40;33;01:or=40;31;01:mi=00:su=37;41:sg=30;43:ca=30;41:tw=30;42:ow=34;42:st=37;44:ex=01;32:*.tar=01;31:*.tgz=01;31:*.arc=01;31:*.arj=01;31:*.taz=01;31:*.lha=01;31:*.lz4=01;31:*.lzh=01;31:*.lzma=01;31:*.tlz=01;31:*.txz=01;31:*.tzo=01;31:*.t7z=01;31:*.zip=01;31:*.z=01;31:*.dz=01;31:*.gz=01;31:*.lrz=01;31:*.lz=01;31:*.lzo=01;31:*.xz=01;31:*.zst=01;31:*.tzst=01;31:*.bz2=01;31:*.bz=01;31:*.tbz=01;31:*.tbz2=01;31:*.tz=01;31:*.deb=01;31:*.rpm=01;31:*.jar=01;31:*.war=01;31:*.ear=01;31:*.sar=01;31:*.rar=01;31:*.alz=01;31:*.ace=01;31:*.zoo=01;31:*.cpio=01;31:*.7z=01;31:*.rz=01;31:*.cab=01;31:*.wim=01;31:*.swm=01;31:*.dwm=01;31:*.esd=01;31:*.jpg=01;35:*.jpeg=01;35:*.mjpg=01;35:*.mjpeg=01;35:*.gif=01;35:*.bmp=01;35:*.pbm=01;35:*.pgm=01;35:*.ppm=01;35:*.tga=01;35:*.xbm=01;35:*.xpm=01;35:*.tif=01;35:*.tiff=01;35:*.png=01;35:*.svg=01;35:*.svgz=01;35:*.mng=01;35:*.pcx=01;35:*.mov=01;35:*.mpg=01;35:*.mpeg=01;35:*.m2v=01;35:*.mkv=01;35:*.webm=01;35:*.webp=01;35:*.ogm=01;35:*.mp4=01;35:*.m4v=01;35:*.mp4v=01;35:*.vob=01;35:*.qt=01;35:*.nuv=01;35:*.wmv=01;35:*.asf=01;35:*.rm=01;35:*.rmvb=01;35:*.flc=01;35:*.avi=01;35:*.fli=01;35:*.flv=01;35:*.gl=01;35:*.dl=01;35:*.xcf=01;35:*.xwd=01;35:*.yuv=01;35:*.cgm=01;35:*.emf=01;35:*.ogv=01;35:*.ogx=01;35:*.aac=00;36:*.au=00;36:*.flac=00;36:*.m4a=00;36:*.mid=00;36:*.midi=00;36:*.mka=00;36:*.mp3=00;36:*.mpc=00;36:*.ogg=00;36:*.ra=00;36:*.wav=00;36:*.oga=00;36:*.opus=00;36:*.spx=00;36:*.xspf=00;36:',
  'SSH_CONNECTION' => '172.12.152.106 44832 10.0.0.10 22',
  'LESSCLOSE' => '/usr/bin/lesspipe %s %s',
  'XDG_SESSION_CLASS' => 'user',
  'TERM' => 'xterm',
  'LESSOPEN' => '| /usr/bin/lesspipe %s',
  'USER' => 'ubuntu',
  'SHLVL' => '1',
  'XDG_SESSION_ID' => '531',
  'XDG_RUNTIME_DIR' => '/run/user/1000',
  'SSH_CLIENT' => '172.12.152.106 44832 22',
  'XDG_DATA_DIRS' => '/usr/local/share:/usr/share:/var/lib/snapd/desktop',
  'DBUS_SESSION_BUS_ADDRESS' => 'unix:path=/run/user/1000/bus',
  'SSH_TTY' => '/dev/pts/0',
  'OLDPWD' => '/var/www/phpcore',
  '_' => '/usr/bin/php',
  'PATH_TRANSLATED' => '',
  'argv' => '[]',
  'argc' => 1,
];

$base = array_unique(array_merge($vars['php'], $vars['cli'], $vars['apc']));
sort($base);
?>
<table border="1">
    <thead>
        <tr>
            <td>Name</td>
            <td>php</td>
            <td>cli</td>
            <td>apc</td>
            <td>example</td>
        </tr>
    </thead>
    <tbody>
<?php
foreach ($base as $var) {
?>
        <tr>
            <td><?php echo $var; ?></td>
            <td><?php echo (in_array($var,$vars['php'])?'X':''); ?></td>
            <td><?php echo (in_array($var,$vars['cli'])?'X':''); ?></td>
            <td><?php echo (in_array($var,$vars['apc'])?'X':''); ?></td>
            <td><?php echo $example[$var]; ?></td>
        </tr>
<?php
}
?>
    </tbody>
</table>