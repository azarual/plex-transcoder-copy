# plex-transcoder-copy
Copy Optimised Versions files easily

### Requirements

- PHP (tested with 5.6.31 on a QNAP server)
- TV Show Folders must be named `TV Show (1980)` style. The brackets and their contents are ignored.

### Installing

Download and edit the file putting your root file path in the correct place.

```php
$root = "/share/Multimedia/TV Shows";
```

### Running

```bash
php plex-transcoder-copy.php
```

This will delete the original file, but not the optimised version. The next time Plex scans the source files it will do that automatically. It only deletes the file after the new one has been successfully copied over.

If the new file has already been copied then this will ignore it (and report "`Skipped xyz, s01e23.mp4`")

### Bugs

- It can't handle dots (or any other special character) at the end of the show name - Plex changes them into an underscore, and I've not bothered to handle that special case.
