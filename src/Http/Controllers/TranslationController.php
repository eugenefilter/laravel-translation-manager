<?php

namespace Oleinykov\LaravelTranslationManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class TranslationController extends Controller
{
	public function index()
	{
		$langPath = lang_path();
		$languageDirs = File::directories($langPath);

		$languages = [];
		$allTranslations = []; // New array to hold all translations for search

		foreach ($languageDirs as $dir) {
			$langCode = basename($dir);
			$fileTree = $this->buildFileTree($dir, $dir);
			$languages[$langCode] = $fileTree;

			// Collect all translations for this language
			$this->collectTranslationsForSearch($fileTree, $langCode, $allTranslations, $dir);
		}

		$jsonFiles = collect(File::files($langPath))->filter(function ($file) {
			return $file->getExtension() === 'json';
		});

		if ($jsonFiles->isNotEmpty()) {
			$jsonNodes = $jsonFiles->map(function ($file) {
				return [
					'type' => 'file',
					'name' => $file->getFilename(),
					'path' => $file->getFilename(),
				];
			})->values()->all();
			$languages['_json'] = $jsonNodes;

			// Collect translations from JSON files
			foreach ($jsonNodes as $node) {
				$filePath = lang_path($node['path']);
				if (File::exists($filePath)) {
					$translations = json_decode(File::get($filePath), true);
					$flatTranslations = \Illuminate\Support\Arr::dot($translations);
					foreach ($flatTranslations as $key => $value) {
						$allTranslations[] = [
							'key' => $key,
							'value' => $value,
							'lang' => '_json',
							'file' => $node['path'],
						];
					}
				}
			}
		}

		return view('translation-manager::index', [
			'languages' => $languages,
			'allTranslations' => $allTranslations, // Pass all translations to the view
		]);
	}

	private function buildFileTree($directory, $basePath)
	{
		$nodes = [];
		$items = File::glob($directory . '/*');

		foreach ($items as $item) {
			$itemName = basename($item);
			$relativePath = ltrim(str_replace($basePath, '', $item), '/');

			if (File::isDirectory($item)) {
				$nodes[] = [
					'type' => 'folder',
					'name' => $itemName,
					'children' => $this->buildFileTree($item, $basePath),
				];
			} elseif (File::isFile($item) && pathinfo($item, PATHINFO_EXTENSION) === 'php') {
				$nodes[] = [
					'type' => 'file',
					'name' => $itemName,
					'path' => str_replace('.php', '', $relativePath),
				];
			}
		}
		return $nodes;
	}

    private function collectTranslationsForSearch($nodes, $langCode, &$allTranslations, $basePath)
    {
        foreach ($nodes as $node) {
            if ($node['type'] === 'file') {
                $filePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $node['path']) . '.php';
                if (File::exists($filePath)) {
                    $translations = include $filePath;
                    $flatTranslations = \Illuminate\Support\Arr::dot($translations);
                    foreach ($flatTranslations as $key => $value) {
                        // If we are under vendor/*, try to extract real locale from path: vendor/{package}/{locale}/...
                        $displayLang = $langCode;
                        if ($langCode === 'vendor') {
                            $parts = explode('/', $node['path']);
                            if (isset($parts[1]) && $parts[1] !== '') {
                                $displayLang = $parts[1];
                            }
                        }
                        $allTranslations[] = [
                            'key' => $key,
                            'value' => $value,
                            'lang' => $displayLang,
                            'file' => $node['path'],
                        ];
                    }
                }
            } elseif ($node['type'] === 'folder') {
                $this->collectTranslationsForSearch($node['children'], $langCode, $allTranslations, $basePath);
            }
        }
    }

	    public function show($path)
    {
        $filePath = "";
        $translations = [];
        $lang = '';
        $file = '';

        if (str_starts_with($path, '_json/')) {
            $lang = '_json';
            $file = substr($path, strlen('_json/'));
            $filePath = lang_path($file);
            if (File::exists($filePath)) {
                $translations = json_decode(File::get($filePath), true);
            }
        } else {
            // Assuming the path is like 'en/messages' or 'en/subfolder/messages'
            $parts = explode('/', $path, 2); // Split into lang and rest of the path
            $lang = $parts[0];
            $file = $parts[1] ?? ''; // The file name without .php extension

            $filePath = lang_path($lang . '/' . $file . '.php');
            if (File::exists($filePath)) {
                $translations = include $filePath;
            }
        }

        if (empty($translations) || !is_array($translations)) {
            $translations = [];
        }

        $flatTranslations = \Illuminate\Support\Arr::dot($translations);
        \ksort($flatTranslations);

        // Convert to an indexed array of key-value pairs for Alpine.js
        $alpineTranslations = [];
        foreach ($flatTranslations as $key => $value) {
            $alpineTranslations[] = ['key' => $key, 'value' => $value];
        }

        return view('translation-manager::show', [
            'lang' => $lang,
            'file' => $file,
            'translations' => $alpineTranslations,
        ]);
    }

	public function update(Request $request, $path)
	{
		$translations = $request->input('translations', []);
		$newData = [];

		// Rebuild the nested array from the flat key-value pairs
		foreach ($translations as $translation) {
			if (!empty($translation['key'])) {
				\Illuminate\Support\Arr::set($newData, $translation['key'], $translation['value']);
			}
		}

		$content = "";
		$lang = '';
		$file = '';

		if (str_starts_with($path, '_json/')) {
			$lang = '_json';
			$file = substr($path, strlen('_json/'));
			$filePath = lang_path($file);
			$content = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		} else {
			$parts = explode('/', $path, 2);
			$lang = $parts[0];
			$file = $parts[1] ?? '';

			$filePath = lang_path($lang . '/' . $file . '.php');
			$exported = var_export($newData, true);
			$content = "<?php\n\nreturn " . $exported . ";\n";
		}

		File::put($filePath, $content);

		return back()->with('success', 'Translations have been saved successfully!');
	}

	public function create()
	{
		// Logic to show the form for creating a new file
		return view('translation-manager::create');
	}

	public function store(Request $request)
	{
		$request->validate([
			'lang' => 'required|string|max:255',
			'file' => 'required|string|max:255',
		]);

		$lang = $request->input('lang');
		$file = $request->input('file');

		if ($lang === '_json') {
			$filePath = lang_path($file . '.json');
			$directory = dirname($filePath);

			if (!File::exists($directory)) {
				File::makeDirectory($directory, 0755, true);
			}

			if (File::exists($filePath)) {
				return back()->withErrors(['file' => 'File already exists.'])->withInput();
			}
			File::put($filePath, json_encode([], JSON_PRETTY_PRINT));
			return redirect()->route('translations.show', ['path' => '_json/' . $file . '.json']);
		} else {
			$langDir = lang_path($lang);
			if (!File::exists($langDir)) {
				File::makeDirectory($langDir);
			}

			$fullFilePath = $langDir . '/' . $file . '.php';
			$directory = dirname($fullFilePath);

			if (!File::exists($directory)) {
				File::makeDirectory($directory, 0755, true); // Recursive create
			}

			if (File::exists($fullFilePath)) {
				return back()->withErrors(['file' => 'File already exists.'])->withInput();
			}

			$content = "<?php\n\nreturn [];\n";
			File::put($fullFilePath, $content);

			return redirect()->route('translations.show', ['path' => $lang . '/' . $file]);
		}
	}

	private function getAllTranslationFiles($grouped = false)
	{
		$langPath = lang_path();
		$files = [];
		$exclude = $this->getMergedExclusions();

		$isExcluded = function($path) use ($exclude) {
			foreach ($exclude as $excludedPath) {
				if (str_starts_with($path, $excludedPath)) {
					return true;
				}
			}
			return false;
		};

		// PHP files
		foreach (File::allFiles($langPath) as $file) {
			if ($file->getExtension() === 'php') {
				$relativePath = str_replace($langPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
				$relativePath = str_replace('.php', '', $relativePath);

				if ($isExcluded($relativePath)) {
					continue;
				}

				if ($grouped) {
					$parts = explode(DIRECTORY_SEPARATOR, $relativePath, 2);
					$lang = $parts[0];
					$file_path = $parts[1] ?? '';
					if ($file_path) {
						$files[$lang][] = $file_path;
					}
				} else {
					$files[] = $relativePath;
				}
			}
		}

		// JSON files
		foreach (File::files($langPath) as $file) {
			if ($file->getExtension() === 'json') {
				$fileName = $file->getFilename();
				if ($isExcluded($fileName)) {
					continue;
				}
				if ($grouped) {
					$files['_json'][] = $fileName;
				} else {
					$files[] = $fileName;
				}
			}
		}

		if ($grouped) {
			ksort($files);
			foreach ($files as &$langFiles) {
				sort($langFiles);
			}
		} else {
			sort($files);
		}

		return $files;
	}

	private function getLanguageCodes()
	{
		$langPath = lang_path();
		$languageDirs = File::directories($langPath);
		$exclude = $this->getMergedExclusions();

		$codes = [];
		foreach ($languageDirs as $dir) {
			$langCode = basename($dir);
			if (!in_array($langCode, $exclude)) {
				$codes[] = $langCode;
			}
		}
		return $codes;
	}

	public function globalCreate()
	{
		$existingFilesGrouped = $this->getAllTranslationFiles(true);
		$languageCodes = $this->getLanguageCodes();

		// For the datalist, we want just the file names, not the full path.
		$datalist = collect($this->getAllTranslationFiles(false))->map(function($path) {
			if (str_ends_with($path, '.json')) {
				return $path;
			}
			return str_contains($path, '/') ? substr($path, strpos($path, '/') + 1) : $path;
		})->unique()->sort()->values()->all();


		return view('translation-manager::global-create', [
			'existingFilesGrouped' => $existingFilesGrouped,
			'datalist' => $datalist,
			'languageCodes' => $languageCodes,
		]);
	}

    public function globalStore(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'file' => 'required|string|max:255',
            'translations' => 'required|array',
            'translations.*' => 'nullable|string',
        ]);

        $newKey = $request->input('key');
        $fileName = $request->input('file');
        $translations = $request->input('translations');
        $selectedLangs = $request->input('languages_to_update', []);
        $langPath = lang_path();
        $exclude = $this->getMergedExclusions();

		// Double-check that the file itself is not excluded
		if (in_array($fileName, $exclude)) {
			return back()->withErrors(['file' => 'This file is excluded from global management.'])->withInput();
		}

        if (str_ends_with($fileName, '.json')) {
            if (!empty($selectedLangs) && !in_array('_json', $selectedLangs)) {
                return back()->with('success', 'Global key added successfully!');
            }
            // For JSON files, we'll just update the selected file with the first translation value provided.
            $filePath = $langPath . '/' . $fileName;
            $existingTranslations = [];
            if (File::exists($filePath)) {
                $existingTranslations = json_decode(File::get($filePath), true);
            }
            
            $newValue = reset($translations);

            \Illuminate\Support\Arr::set($existingTranslations, $newKey, $newValue);
            File::put($filePath, json_encode($existingTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        } else {
            // For PHP files, we iterate through the provided translations and update each language's file.
            foreach ($translations as $langCode => $newValue) {
                // Skip if the language code is not valid or excluded
                if (!in_array($langCode, $this->getLanguageCodes())) {
                    continue;
                }
                if (!empty($selectedLangs) && !in_array($langCode, $selectedLangs)) {
                    continue;
                }

                $filePath = $langPath . '/' . $langCode . '/' . $fileName . '.php';
                
                $existingTranslations = [];
                if (File::exists($filePath)) {
					$existingTranslations = include $filePath;
				}

				\Illuminate\Support\Arr::set($existingTranslations, $newKey, $newValue);

				$directory = dirname($filePath);
				if (!File::exists($directory)) {
					File::makeDirectory($directory, 0755, true);
				}

				$exported = var_export($existingTranslations, true);
				$content = "<?php\n\nreturn " . $exported . ";\n";
				File::put($filePath, $content);
			}
		}

		return back()->with('success', 'Global key added successfully!');
	}

	public function createFolder(Request $request)
	{
		$request->validate([
			'folder' => 'required|string|max:255',
			'locale' => 'required|string|max:255',
		]);

		$folderName = $request->input('folder');
		$targetLocale = $request->input('locale');

		if ($targetLocale === 'all') {
			$langPath = lang_path();
			$languageDirs = File::directories($langPath);
			foreach ($languageDirs as $dir) {
				$folderPath = $dir . '/' . $folderName;
				if (!File::exists($folderPath)) {
					File::makeDirectory($folderPath, 0755, true);
				}
			}
		} else {
			$folderPath = lang_path($targetLocale . '/' . $folderName);
			if (!File::exists($folderPath)) {
				File::makeDirectory($folderPath, 0755, true);
			}
		}

		return back()->with('success', 'Folder created successfully!');
	}

    public function globalEdit($key)
    {
        // Ensure the key is properly decoded (handles unicode and special chars)
        $key = urldecode($key);

        $languageCodes = $this->getLanguageCodes();
        $translations = [];
        $allFiles = $this->getAllTranslationFiles();

        $langsWithKey = [];
        $referenceNormal = null; // ['relative' => 'path/inside/lang']
        $referenceVendor = null;  // ['package' => 'pkg', 'relative' => 'path/inside/lang']

        foreach ($allFiles as $filePath) {
            $fullPath = lang_path() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath) . '.php';
            if (str_ends_with($filePath, '.json')) {
                $fullPath = lang_path() . DIRECTORY_SEPARATOR . $filePath;
            }

			if (!File::exists($fullPath)) {
				continue;
			}

            if (str_ends_with($filePath, '.json')) {
                $content = json_decode(File::get($fullPath), true);
                $lang = str_replace('.json', '', basename($filePath));
            } else {
                $content = include $fullPath;
                $parts = explode(DIRECTORY_SEPARATOR, $filePath);
                // Support vendor overrides: resources/lang/vendor/{package}/{locale}/...
                if (($parts[0] ?? null) === 'vendor' && isset($parts[2])) {
                    $lang = $parts[2];
                } else {
                    $lang = $parts[0] ?? '';
                }
            }

			if (\Illuminate\Support\Arr::has($content, $key)) {
				$translations[$lang] = \Illuminate\Support\Arr::get($content, $key);
			}
		}

		return view('translation-manager::global-edit', compact('key', 'languageCodes', 'translations'));
	}

    public function globalUpdate(Request $request, $key)
    {
        // Ensure the key is properly decoded (handles unicode and special chars)
        $key = urldecode($key);
        $request->validate([
            'new_key' => 'required|string|max:255',
            'translations' => 'required|array',
            'translations.*' => 'nullable|string',
            'languages_to_update' => 'sometimes|array', // Can be empty if no boxes are checked
		]);

        $newKey = $request->input('new_key');
		$formTranslations = $request->input('translations');
        $languagesToUpdate = $request->input('languages_to_update', []);
		$allFiles = $this->getAllTranslationFiles();
        $updatedTranslations = [];
        $langsWithKey = [];
        $referenceNormal = null; // ['relative' => 'path/inside/lang']
        $referenceVendor = null;  // ['package' => 'pkg', 'relative' => 'path/inside/lang']

		foreach ($allFiles as $filePath) {
			$fullPath = lang_path() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath) . '.php';
			if (str_ends_with($filePath, '.json')) {
				$fullPath = lang_path() . DIRECTORY_SEPARATOR . $filePath;
			}

			if (!File::exists($fullPath)) {
				continue;
			}

            if (str_ends_with($filePath, '.json')) {
                $content = json_decode(File::get($fullPath), true);
                $lang = '_json';
            } else {
                $content = include $fullPath;
                $parts = explode(DIRECTORY_SEPARATOR, $filePath);
                // Support vendor overrides: resources/lang/vendor/{package}/{locale}/...
                if (($parts[0] ?? null) === 'vendor' && isset($parts[2])) {
                    $lang = $parts[2];
                } else {
                    $lang = $parts[0] ?? '';
                }
            }

            if (Arr::has($content, $key)) {
                $value = Arr::get($content, $key);
                $langsWithKey[$lang] = true;

                // Capture a reference path to create missing language files later
                if (!str_ends_with($filePath, '.json')) {
                    $parts = explode(DIRECTORY_SEPARATOR, $filePath);
                    if (($parts[0] ?? null) === 'vendor' && isset($parts[1], $parts[2])) {
                        if ($referenceVendor === null) {
                            $referenceVendor = [
                                'package' => $parts[1],
                                'relative' => implode(DIRECTORY_SEPARATOR, array_slice($parts, 3)),
                            ];
                        }
                    } else {
                        if ($referenceNormal === null) {
                            $referenceNormal = [
                                'relative' => implode(DIRECTORY_SEPARATOR, array_slice($parts, 1)),
                            ];
                        }
                    }
                }

                // Only update if the language was selected
                if (in_array($lang, $languagesToUpdate)) {
                    $value = $formTranslations[$lang] ?? $value;

                    // If key has changed, remove the old one
                    if ($key !== $newKey) {
                        Arr::forget($content, $key);
                    }
                    
					Arr::set($content, $newKey, $value);

					if (str_ends_with($filePath, '.json')) {
						File::put($fullPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
					} else {
						$exported = var_export($content, true);
						File::put($fullPath, "<?php\n\nreturn " . $exported . ";\n");
					}
                }
                $updatedTranslations[$lang] = $value;
            }
		}

        // Create missing language files/entries for selected languages that don't have the key yet
        foreach ($languagesToUpdate as $targetLang) {
            if ($targetLang === '_json') { continue; }
            if (isset($langsWithKey[$targetLang])) { continue; }
            $newValue = $formTranslations[$targetLang] ?? null;
            if ($newValue === null) { continue; }

            // Decide where to create: prefer vendor reference if available, else normal
            $newFullPath = null;
            if ($referenceVendor !== null) {
                $newFullPath = lang_path()
                    . DIRECTORY_SEPARATOR . 'vendor'
                    . DIRECTORY_SEPARATOR . $referenceVendor['package']
                    . DIRECTORY_SEPARATOR . $targetLang
                    . DIRECTORY_SEPARATOR . $referenceVendor['relative'] . '.php';
            } elseif ($referenceNormal !== null) {
                $newFullPath = lang_path()
                    . DIRECTORY_SEPARATOR . $targetLang
                    . DIRECTORY_SEPARATOR . $referenceNormal['relative'] . '.php';
            }

            if ($newFullPath) {
                $dir = dirname($newFullPath);
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $existing = [];
                if (File::exists($newFullPath)) {
                    $existing = include $newFullPath;
                }
                Arr::set($existing, $newKey, $newValue);
                $exported = var_export($existing, true);
                File::put($newFullPath, "<?php\n\nreturn " . $exported . ";\n");
                $updatedTranslations[$targetLang] = $newValue;
            }
        }

        // Handle JSON creation/update across all locales when requested
        if (in_array('_json', $languagesToUpdate, true)) {
            $jsonValue = $formTranslations['_json'] ?? null;
            if ($jsonValue !== null) {
                foreach ($this->getLanguageCodes() as $code) {
                    $jsonPath = lang_path($code . '.json');
                    $jsonDir = dirname($jsonPath);
                    if (!File::exists($jsonDir)) {
                        File::makeDirectory($jsonDir, 0755, true);
                    }
                    $jsonContent = [];
                    if (File::exists($jsonPath)) {
                        $decoded = json_decode(File::get($jsonPath), true);
                        if (is_array($decoded)) {
                            $jsonContent = $decoded;
                        }
                    }
                    Arr::set($jsonContent, $newKey, $jsonValue);
                    File::put($jsonPath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                $updatedTranslations['_json'] = $jsonValue;
            }
        }

        $languageCodes = $this->getLanguageCodes();

		return view('translation-manager::global-edit', [
            'key' => $newKey,
            'languageCodes' => $languageCodes,
            'translations' => $updatedTranslations,
            'success' => 'Translations for key "' . $newKey . '" have been updated.'
        ]);
	}

	public function globalDestroy($key)
	{
		$allFiles = $this->getAllTranslationFiles();

		foreach ($allFiles as $filePath) {
			$fullPath = lang_path() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath) . '.php';
			if (str_ends_with($filePath, '.json')) {
				$fullPath = lang_path() . DIRECTORY_SEPARATOR . $filePath;
			}
			
			if (!File::exists($fullPath)) {
				continue;
			}

			if (str_ends_with($filePath, '.json')) {
				$content = json_decode(File::get($fullPath), true);
			} else {
				$content = include $fullPath;
			}

			if (\Illuminate\Support\Arr::has($content, $key)) {
				\Illuminate\Support\Arr::forget($content, $key);

				if (str_ends_with($filePath, '.json')) {
					File::put($fullPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				} else {
					$exported = var_export($content, true);
					File::put($fullPath, "<?php\n\nreturn " . $exported . ";\n");
				}
			}
		}

		return redirect()->route('translations.index')->with('success', 'Key "' . $key . '" has been deleted from all files.');
	}

    public function destroyLanguage($lang)
	{
		$langPath = lang_path($lang);

		// Basic validation to prevent accidental deletion of root or special folders
		if (empty($lang) || $lang === '_json' || !File::isDirectory($langPath)) {
			return back()->withErrors(['error' => 'Invalid language folder or folder not found.']);
		}

		try {
			File::deleteDirectory($langPath);
			return back()->with('success', "Language folder '{$lang}' and its contents deleted successfully!");
		} catch (\Exception $e) {
			return back()->withErrors(['error' => 'Failed to delete language folder: ' . $e->getMessage()]);
		}
	}

    private function getExclusionsStoragePath()
    {
        return storage_path('app/translation-manager-exclusions.json');
    }

    private function getMergedExclusions()
    {
        $configExclusions = config('translation-manager.exclude_from_global', []);
        $storagePath = $this->getExclusionsStoragePath();
        
        if (!File::exists($storagePath)) {
            return $configExclusions;
        }

        $userExclusions = json_decode(File::get($storagePath), true);
        
        if (!is_array($userExclusions)) {
            return $configExclusions;
        }

        return array_unique(array_merge($configExclusions, $userExclusions));
    }

	public function showExclusions()
    {
        $exclusions = $this->getMergedExclusions();
        return view('translation-manager::exclusions', compact('exclusions'));
    }

    public function updateExclusions(Request $request)
    {
        $request->validate([
            'exclusions' => 'nullable|string',
        ]);

        $exclusionsInput = $request->input('exclusions', '');
        $exclusions = array_filter(array_map('trim', explode("\n", $exclusionsInput)));
        
        $storagePath = $this->getExclusionsStoragePath();
        File::put($storagePath, json_encode(array_values($exclusions), JSON_PRETTY_PRINT));

        return back()->with('success', 'Exclusion list has been updated.');
    }

	public function copyCreate($lang, $file)
	{
		return view('translation-manager::copy', compact('lang', 'file'));
	}

	public function copyStore(Request $request)
	{
		$request->validate([
			'lang' => 'required|string|max:255',
			'file' => 'required|string|max:255',
			'new_file' => 'required|string|max:255',
		]);

		$lang = $request->input('lang');
		$file = $request->input('file');
		$newFile = $request->input('new_file');

		$sourcePath = '';
		$destinationPath = '';

		if ($lang === '_json') {
			$sourcePath = lang_path($file);
			$destinationPath = lang_path($newFile . '.json');
		} else {
			$sourcePath = lang_path($lang . '/' . $file . '.php');
			$destinationPath = lang_path($lang . '/' . $newFile . '.php');
		}

		if (!File::exists($sourcePath)) {
			return back()->withErrors(['error' => 'Source file not found.']);
		}

		if (File::exists($destinationPath)) {
			return back()->withErrors(['error' => 'Destination file already exists.']);
		}

		try {
			File::copy($sourcePath, $destinationPath);
			return redirect()->route('translations.show', ['path' => ($lang === '_json' ? '_json/' . $newFile . '.json' : $lang . '/' . $newFile)])->with('success', 'File copied successfully!');
		} catch (
Exception $e) {
			return back()->withErrors(['error' => 'Failed to copy file: ' . $e->getMessage()]);
		}
	}

	/**
	 * Show per-file cross-language matrix editor.
	 * The selected $path comes from the tree, like:
	 *  - "en/messages" (PHP)
	 *  - "vendor/package/en/messages" (PHP vendor)
	 *  - "_json/en.json" (JSON)
	 */
	public function fileMatrix($path)
	{
		$languageCodes = $this->getLanguageCodes();

		$isJson = str_starts_with($path, '_json/');
		$isVendor = false;
		$vendorPackage = null;
		$relative = '';
		$baseLang = '';

		if ($isJson) {
			// e.g. _json/en.json — we will compare all {lang}.json
			$baseLang = basename($path, '.json'); // informational only
		} else {
			$parts = explode('/', $path, 2);
			$first = $parts[0] ?? '';
			$rest = $parts[1] ?? '';
			if ($first === 'vendor') {
				$isVendor = true;
				$v = explode('/', $rest);
				$vendorPackage = $v[0] ?? '';
				$baseLang = $v[1] ?? '';
				$relative = implode('/', array_slice($v, 2));
			} else {
				$baseLang = $first;
				$relative = $rest; // without .php
			}
		}

		$keys = [];
		$values = [];

		if ($isJson) {
			foreach ($languageCodes as $lang) {
				$filePath = lang_path($lang . '.json');
				$data = [];
				if (File::exists($filePath)) {
					$decoded = json_decode(File::get($filePath), true);
					if (is_array($decoded)) { $data = $decoded; }
				}
				$flat = Arr::dot($data);
				$values[$lang] = $flat;
				$keys = array_unique(array_merge($keys, array_keys($flat)));
			}
		} else {
			foreach ($languageCodes as $lang) {
				$filePath = $isVendor
					? lang_path('vendor/' . $vendorPackage . '/' . $lang . '/' . $relative . '.php')
					: lang_path($lang . '/' . $relative . '.php');
				$data = [];
				if (File::exists($filePath)) {
					$data = include $filePath;
				}
				$flat = Arr::dot(is_array($data) ? $data : []);
				$values[$lang] = $flat;
				$keys = array_unique(array_merge($keys, array_keys($flat)));
			}
		}

		sort($keys);

		// Order languages with the base language first
		$orderedLangs = $languageCodes;
		if ($baseLang && in_array($baseLang, $orderedLangs)) {
			$orderedLangs = array_values(array_unique(array_merge([$baseLang], $orderedLangs)));
		}

		return view('translation-manager::file-matrix', [
			'path' => $path,
			'isJson' => $isJson,
			'isVendor' => $isVendor,
			'vendorPackage' => $vendorPackage,
			'relative' => $relative,
			'baseLang' => $baseLang,
			'languageCodes' => $orderedLangs,
			'keys' => $keys,
			'values' => $values,
		]);
	}

	/**
	 * Persist per-file cross-language matrix edits.
	 */
    public function fileMatrixUpdate(Request $request, $path)
    {
        $isJson = str_starts_with($path, '_json/');
        $isVendor = false;
        $vendorPackage = null;
        $relative = '';

		if (!$isJson) {
			$parts = explode('/', $path, 2);
			$first = $parts[0] ?? '';
			$rest = $parts[1] ?? '';
			if ($first === 'vendor') {
				$isVendor = true;
				$v = explode('/', $rest);
				$vendorPackage = $v[0] ?? '';
				$relative = implode('/', array_slice($v, 2));
			} else {
				$relative = $rest; // without .php
			}
		}

        $input = $request->input('translations', []); // [lang => [dot.key => value]]
        // Support adding a new key across languages from the matrix UI
        $newKey = trim((string) $request->input('new_key', ''));
        $newTranslations = $request->input('new_translations', []); // [lang => value]
        if ($newKey !== '') {
            foreach ((array) $newTranslations as $lang => $val) {
                if (!isset($input[$lang]) || !is_array($input[$lang])) {
                    $input[$lang] = [];
                }
                $input[$lang][$newKey] = $val;
            }
        }
        $languageCodes = $this->getLanguageCodes();

		foreach ($input as $lang => $flatMap) {
			if (!in_array($lang, $languageCodes)) { continue; }
			$flatMap = is_array($flatMap) ? $flatMap : [];
			$nested = [];
			foreach ($flatMap as $dotKey => $value) {
				if ($dotKey === '' || $dotKey === null) { continue; }
				Arr::set($nested, $dotKey, $value);
			}

			if ($isJson) {
				$filePath = lang_path($lang . '.json');
				$dir = dirname($filePath);
				if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
				// Merge with existing to preserve keys not in matrix
				$existing = [];
				if (File::exists($filePath)) {
					$decoded = json_decode(File::get($filePath), true);
					if (is_array($decoded)) { $existing = $decoded; }
				}
				$merged = array_replace_recursive($existing, $nested);
				File::put($filePath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			} else {
				$filePath = $isVendor
					? lang_path('vendor/' . $vendorPackage . '/' . $lang . '/' . $relative . '.php')
					: lang_path($lang . '/' . $relative . '.php');
				$dir = dirname($filePath);
				if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
				// Merge with existing to preserve keys not in matrix
				$existing = [];
				if (File::exists($filePath)) {
					$existing = include $filePath;
				}
				if (!is_array($existing)) { $existing = []; }
				$merged = array_replace_recursive($existing, $nested);
				$exported = var_export($merged, true);
				File::put($filePath, "<?php\n\nreturn " . $exported . ";\n");
			}
		}

        return back()->with('success', 'Matrix changes saved.');
    }

	/**
	 * AJAX: update a single cell (one lang+key) without reload.
	 */
	public function fileMatrixUpdateCell(Request $request, $path)
	{
		$request->validate([
			'lang' => 'required|string',
			'key' => 'required|string',
			'value' => 'nullable|string',
		]);

		$isJson = str_starts_with($path, '_json/');
		$isVendor = false;
		$vendorPackage = null;
		$relative = '';

		if (!$isJson) {
			$parts = explode('/', $path, 2);
			$first = $parts[0] ?? '';
			$rest = $parts[1] ?? '';
			if ($first === 'vendor') {
				$isVendor = true;
				$v = explode('/', $rest);
				$vendorPackage = $v[0] ?? '';
				$relative = implode('/', array_slice($v, 2));
			} else {
				$relative = $rest;
			}
		}

		$lang = $request->input('lang');
		$key = $request->input('key');
		$value = $request->input('value');

        if ($isJson) {
            $filePath = lang_path($lang . '.json');
            $dir = dirname($filePath);
            if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
            $content = [];
            if (File::exists($filePath)) {
                $decoded = json_decode(File::get($filePath), true);
                if (is_array($decoded)) { $content = $decoded; }
            }
            Arr::set($content, $key, $value);
            $bytes = File::put($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($bytes === false) {
                return response()->json(['status' => 'error', 'message' => 'Failed to write JSON file'], 500);
            }
        } else {
            $filePath = $isVendor
                ? lang_path('vendor/' . $vendorPackage . '/' . $lang . '/' . $relative . '.php')
                : lang_path($lang . '/' . $relative . '.php');
            $dir = dirname($filePath);
            if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
            $content = [];
            if (File::exists($filePath)) {
                $content = include $filePath;
            }
            if (!is_array($content)) { $content = []; }
            Arr::set($content, $key, $value);
            $exported = var_export($content, true);
            $bytes = File::put($filePath, "<?php\n\nreturn " . $exported . ";\n");
            if ($bytes === false) {
                return response()->json(['status' => 'error', 'message' => 'Failed to write PHP file'], 500);
            }
        }

        return response()->json(['status' => 'ok']);
    }

	/**
	 * AJAX: add a new key across languages from matrix.
	 */
	public function fileMatrixAddKey(Request $request, $path)
	{
		$request->validate([
			'new_key' => 'required|string',
			'new_translations' => 'array',
		]);

		$isJson = str_starts_with($path, '_json/');
		$isVendor = false;
		$vendorPackage = null;
		$relative = '';

		if (!$isJson) {
			$parts = explode('/', $path, 2);
			$first = $parts[0] ?? '';
			$rest = $parts[1] ?? '';
			if ($first === 'vendor') {
				$isVendor = true;
				$v = explode('/', $rest);
				$vendorPackage = $v[0] ?? '';
				$relative = implode('/', array_slice($v, 2));
			} else {
				$relative = $rest;
			}
		}

		$newKey = $request->input('new_key');
		$map = $request->input('new_translations', []); // [lang => value]
		$languageCodes = $this->getLanguageCodes();

        foreach ($map as $lang => $val) {
            if (!in_array($lang, $languageCodes)) { continue; }
            if ($isJson) {
                $filePath = lang_path($lang . '.json');
                $dir = dirname($filePath);
                if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
                $content = [];
                if (File::exists($filePath)) {
                    $decoded = json_decode(File::get($filePath), true);
                    if (is_array($decoded)) { $content = $decoded; }
                }
                Arr::set($content, $newKey, $val);
                $bytes = File::put($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if ($bytes === false) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to write JSON file for ' . $lang], 500);
                }
            } else {
                $filePath = $isVendor
                    ? lang_path('vendor/' . $vendorPackage . '/' . $lang . '/' . $relative . '.php')
                    : lang_path($lang . '/' . $relative . '.php');
                $dir = dirname($filePath);
                if (!File::exists($dir)) { File::makeDirectory($dir, 0755, true); }
                $content = [];
                if (File::exists($filePath)) {
                    $content = include $filePath;
                }
                if (!is_array($content)) { $content = []; }
                Arr::set($content, $newKey, $val);
                $exported = var_export($content, true);
                $bytes = File::put($filePath, "<?php\n\nreturn " . $exported . ";\n");
                if ($bytes === false) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to write PHP file for ' . $lang], 500);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
