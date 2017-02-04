# UTStats
UTStats is a Serverside Actor and websystem for the original Unreal Tournament
that generates statistics from custom NGStats log files.
It was originally created by azazel, AnthraX and toa, with additions by Skillz, killereye, Enakin, Loki and rork.
This is a modified version that addresses some bugs and deprecation warnings.

#### Requirements:
- Access to the Unreal Tournament log files
- PHP & MySQL enabled webserver
- PHP needs to allow ftpconnect etc. if you wish to use auto-ftp of logs

#### Installation:
This readme will guide you through the installation process.

1. Installing the Server Actor
2. Installing the Web stuff (full install and update)
3. Importing Logs

## 1. UTStats Actor Installation

From the System folder in the zip file, copy to your UTServer\System folder the
following files:

UTSAccuBeta4_2.u
UTStatsBeta4_2.u

Remove any previous entries for UTStats and UTSAccuBeta from `[Engine.GameEngine]`.

Add to `[Engine.GameEngine]` the following lines:

```ini
ServerPackages=UTSAccuBeta4_2
ServerActors=UTStatsBeta4_2.UTStatsSA
```

Under the [Engine.GameInfo] check for:
`bLocalLog=True` or `bLocalLog=False`

This line HAS to be:
```ini
bLocalLog=False
```

Setting it to True will not give you complete logs (don't ask why it just won't).

Log files are recorded to the UTServer\Logs folder.

## 2. UTStats Web Installation

From the html directory in the zip file, upload all the files to your webserver
wherever you like. I suggest under a directory called utstats.

Now edit the **config.php** file.
Enter in your **mysql database**, **hostname**, **username** and **password** at the top.
You must set an **adminkey** in here or you won't be able to import/use the admin
feature.
Check all the other options to see if they are as you wish (they are set to
what we consider the best options).
If you are going to use ftp transferring of log files amend the FTP connection
as required.

Now visit the **admin page** and click **"Check Server Settings"** near the bottom.
This will setup the database, if the tables already exist it will keep the
old tables. It will also check the file permissions. If it can't set the file
permissions you'll have to do it yourself,

Using whatever means (usually the ftp client) you need to CHMOD the **logs** folder to 777.  
Now go into the logs folder and CHMOD the **backup**, **utdc**, **ac** and **ace** folders to 777.  
Now go back a level and into the includes folder and CHMOD the file **ftptimestamp.php** to 777.

```shell
chmod 777 logs
chmod 777 logs/backup
chmod 777 logs/utdc
chmod 777 logs/ac
chmod 777 logs/ace
chmod 777 includes/ftptimestamp.php
```

Optionally, you can add images of maps:

- [Standard pack](http://www.ut-files.com/index.php?dir=Stats/&file=utstats_maps1.zip)
- [Additional CTF pack](http://www.rork.nl/junkyard/downloads/files/utstats_maps_ctf1.zip)
- [Enakins pack](http://if36g.ho.ua/files/img.rar)

There's also a tool that extracts screenshots [here](http://www.unrealadmin.org/forums/showthread.php?t=29928).

### Update from version 4.2 and newer

The current install is compatible with the database of version 4.2 and later.
However bunny track stats are only available from version 4.2.5.

Just remove all the files/folders (apart from the logs folder, if you wish to
keep your backup logs), and upload all the files in the html folder to your
webserver.

Then visit the admin area and click "Check Server Settings" near the bottom of
the list. This will install databases if they're missing and fix the Bunny
Track captimes to the new format if needed.

If you use your old config.php make sure the following section is included:

```php
// Screenshot files start with...
$import_utdc_screenshot_start = "[UTDC]";

// Screenshot files end with...
$import_utdc_screenshot_extension = ".enc";

// UTStats can download and manage your ACE logs
// Enable downloading of ACE logs?
$import_ace_download_enable = false;

// Log files start with...
$import_ace_log_start = "[ACE]";

// Log files end with...
$import_ace_log_extension = ".log";

// Log files start with...
$import_ace_screenshot_start = "[ACE]";

// Log files end with...
$import_ace_screenshot_extension = ".jpg";

// Compress ACE logfiles after download? [compress/gzip/no]
// (see $import_log_backup for available options)
$import_ace_log_compress = "compress";

// Purge old ACE logs after x days. 0 to disable.
$import_ace_log_purge_after = 0;
```

### Update from versions older than 4.2:

Firstly we don't advise using any old logs, you will not benefit from any new
features AND it will likely mess up some page data.
We apologize for not being able to save this information but there are a
massive amount of new features that are well worth ditching the old logs for :)

If you do wish to keep the old logs you need to do a full install anyway, so
drop all the tables you had and remove all the
files/folders (making sure you don't delete the logs backups).
When you edit config.php make sure to change the option:

```php
$import_incompatible_logs=false;
```
to
```php
$import_incompatible_logs=true;
```

If you are wisely going to ditch the old logs just clear out your old install
and all the tables and start from here.

## 3. Importing Logs

First make sure you edit config.php and set an **adminkey** at the top!
Depending on the level of access you have and how competent you are you can do this 4 ways.

**Option 1:**
If you don't have ftp access to your UT server or allowed to use ftp via php then upload your
logs manually to the logs folder and then run the importer via the Import link.

**Option 2:**
If you have ftp access and can do ftp via php, edit the config.php file and input your UT servers ftp
information into there. Now run the importer via the Import link.
Additionally you can use [UTStats Trigger](http://ut.fuzzeh.com/serverfiles/UTSTATS_Trigger.zip)
Mutator, which triggers UTStats import after every map switch.

**Option 3:**
Automate the process using a cron job or scheduled task.
Just get this to run cd to the root of stats (where import.php is) and execute the import.php file with php.

**Option 4:**
Automate the web process using a web service cron job.

## License

Copyright (C) 2004/2005 azazel, AnthraX and toa.

This program is free software; you can redistribute and/or modify
it under the terms of the Open Unreal Mod License.
See LICENSE for more information.
