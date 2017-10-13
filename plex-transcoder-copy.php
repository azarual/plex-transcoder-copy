<?php

/**
 * Copy transcoded files back to their original places to allow Plex to take up
 * less space. This will rename mp4 -> m4v if needed, and skip already-existing
 * files.
 *
 * @copyright (c) 2017, Ryc O'Chet <rycochet@rycochet.com>
 */
// Get the root TV Show folder
$root = "/share/Multimedia/TV Shows";

function join_paths() {
	$paths = array();

	foreach (func_get_args() as $arg) {
		if ($arg !== '') {
			$paths[] = $arg;
		}
	}
	return preg_replace('#/+#', '/', join('/', $paths));
}

$copy_count = 0;
$skip_count = 0;
chdir($root);
$shows = glob("*");
$episodes_per_show = Array();
sort($shows);

function find_show($name) {
	global $shows;

	$fixed_name = str_replace(")", "", $name);
	foreach ($shows as $show) {
		if (strpos($show, $fixed_name) === 0) {
			return $show;
		}
	}
	return false;
}

function update_episode($show, $episode, $fullpath) {
	global $copy_count, $skip_count;

	$real_show = find_show($show);
	if ($real_show !== false) {
		global $root, $episodes_per_show;

		$basepart = substr($episode, 0, strlen($episode) - 4);
		if (!array_key_exists($real_show, $episodes_per_show)) {
			chdir($real_show);
			$episodes_per_show[$real_show] = glob("*/*");
			chdir($root);
		}
		foreach ($episodes_per_show[$real_show] as $filepath) {
			$split = explode("/", $filepath, 2);
			$season = $split[0];
			$file = $split[1];
			if (stripos($file, $basepart) === 0 && preg_match("/\.(nfo|srt)$/", $file) === 0) {
				$new_name = substr($file, 0, strlen($file) - 4) . ".mp4";
				$target = join_paths($real_show, $season, $new_name);
				$episode_size = filesize($fullpath);
				$m4v_name = substr($file, 0, strlen($file) - 4) . ".m4v";
				$m4vtarget = join_paths($real_show, $season, $m4v_name);
				$skip = false;
				if (file_exists($target)) {
					$target_size = filesize($target);
					$target = $m4vtarget;
					$skip = $target_size === false || $episode_size === false || $target_size === $episode_size;
				} elseif (file_exists($m4vtarget) && filesize($m4vtarget) === $episode_size) {
					$skip = true;
				}
				if ($skip) {
					print("Skipped $real_show, $episode\n");
					$skip_count++;
				} elseif (copy($fullpath, $target)) {
					unlink(join_paths($real_show, $filepath));
					print("Copied $real_show, $episode\n");
					$copy_count++;
				}
				return;
			}
		}
	} else {
		print("Not found: $show\n");
	}
}

if (is_dir("Plex Versions")) {
	foreach (glob("Plex Versions/*/*/*.mp4") as $path) {
		$split = explode("/", $path);

		update_episode($split[2], $split[3], $path);
	}
}
print("Finished. Copied $copy_count files, skipped $skip_count files.\n");
