<?php

namespace App;

use function Accela\env;

// Front Matterとマークダウン本文を分離する関数
function parseFrontMatter($markdown) {
  if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $markdown, $matches)) {
    $yaml = $matches[1];
    $content = $matches[2];

    // 簡易YAMLパース（key: value形式のみ対応）
    $meta = [];
    foreach (explode("\n", $yaml) as $line) {
      if (preg_match('/^(\w+):\s*(.+)$/', trim($line), $m)) {
        $meta[$m[1]] = trim($m[2]);
      }
    }

    return ['meta' => $meta, 'content' => $content];
  }

  return ['meta' => [], 'content' => $markdown];
}

// マークダウンファイルからタイトルを抽出する関数
function extractTitle($markdown, $filePath = null) {
  // Front Matterのtitleを優先
  $parsed = parseFrontMatter($markdown);
  if (!empty($parsed['meta']['title'])) {
    return $parsed['meta']['title'];
  }

  // マークダウン本文から抽出
  if (preg_match('/^#\s+(.+)$/m', $parsed['content'], $matches)) {
    return trim($matches[1]);
  }

  // ファイル名から抽出
  if ($filePath) {
    $basename = basename($filePath, '.md');

    // index.md の場合はディレクトリ名を使用
    if ($basename === 'index') {
      $dirname = basename(dirname($filePath));
      // 数値プレフィックスを除去
      return preg_replace('/^\d+_/', '', $dirname);
    }

    // 数値プレフィックスを除去
    return preg_replace('/^\d+_/', '', $basename);
  }

  return 'Untitled';
}

// descriptionを抽出する関数
function extractDescription($markdown) {
  $parsed = parseFrontMatter($markdown);

  // Front Matterのdescriptionを優先
  if (!empty($parsed['meta']['description'])) {
    return $parsed['meta']['description'];
  }

  // なければ最初の段落から抽出
  $content = $parsed['content'];
  // タイトル（# ...）を除去
  $content = preg_replace('/^#\s+.+$/m', '', $content);

  // 最初の段落を取得
  if (preg_match('/^\s*(.+?)(?:\n\n|$)/s', trim($content), $matches)) {
    $desc = strip_tags($matches[1]);
    $desc = preg_replace('/\s+/', ' ', $desc); // 改行をスペースに
    return mb_substr($desc, 0, 150); // 150文字に制限
  }

  return '';
}

// ファイルパスをURLパスに変換する関数
function filePathToUrlPath($filePath) {
  $dataDir = realpath(env("dataDir", __DIR__ . '/../../data'));
  $relativePath = str_replace($dataDir, '', $filePath);
  $relativePath = str_replace('\\', '/', $relativePath);
  $relativePath = preg_replace('/\.md$/', '', $relativePath);

  // Remove numeric prefixes (01_, 02_, etc.)
  $relativePath = preg_replace('@/\d+_@', '/', $relativePath);

  if (basename($relativePath) === 'index') {
    $dir = dirname($relativePath);
    if ($dir === '/') {
      $relativePath = '/';
    } else {
      $relativePath = $dir . '/';
    }
  }

  return $relativePath;
}

function buildDataFileTree(){
  $root = realpath(env("dataDir", __DIR__ . '/../../data'));

  /**
   * @var callable $walk
   */
  $walk;
  $walk = function($title, $dir)use($root, &$walk){
    $node = ["title" => $title, "children" => []];

    $iterator = new \RecursiveDirectoryIterator($root . $dir,  \RecursiveDirectoryIterator::SKIP_DOTS);

    foreach ($iterator as $item) {
      if ($item->isDir()) {
        $_relPath = "{$dir}{$item->getFilename()}/";
        $node["isGroup"] = true;
        // 数値プレフィックスを除去してタイトルとして使用
        $dirTitle = preg_replace('/^\d+_/', '', $item->getBasename(""));
        $node["children"][] = $walk($dirTitle, $_relPath);

      } else if($item->getExtension() === "md") {
        $filePath = $item->getPathname();
        $markdown = file_get_contents($filePath);
        $relFilePath = str_replace($root, "", $filePath);

        // Front Matterを解析
        $parsed = parseFrontMatter($markdown);

        // modtimeはFront Matterのmodtimeを優先（文字列のまま）、なければファイルのmtimeを使用
        $modtime = !empty($parsed['meta']['modtime'])
          ? $parsed['meta']['modtime']
          : date('Y-m-d H:i:s', $item->getMtime());

        $page = [
          "title" => extractTitle($markdown, $filePath),
          "description" => extractDescription($markdown),
          "path" => filePathToUrlPath($filePath),
          "datapath" => str_replace(".md", "", $relFilePath),
          "modtime" => $modtime,
          "isGroup" => false
        ];

        // Front Matterだけのページは対象外（本文がない）
        $contentWithoutFrontMatter = trim($parsed['content']);
        if(empty($contentWithoutFrontMatter)){
          unset($page["path"]);
          unset($page["modtime"]);
        }

        if($item->getFilename() === "index.md"){
          $node = [
            ...$page,
            "children" => $node["children"]
          ];
        }else{
          $node["children"][] = $page;
        }
      }
    }

    usort($node["children"], function ($a, $b) {
      if(($a["datapath"] ?? $a["title"]) > ($b["datapath"] ?? $b["title"])){
        return 1;
      }
      return 0;
    });


    $node["isGroup"] = count($node["children"]) > 0;
    return $node;
  };

  return $walk("TOP", "/");
}

function flattenNodeProps($node, &$props=[]){
  if(isset($node["path"])){
    $props[$node["path"]] = [
      "title" => $node["title"],
      "description" => $node["description"] ?? "",
      "path" => $node["path"],
      "datapath" => $node["datapath"],
      "modtime" => $node["modtime"],
    ];
  }

  if($node["isGroup"]){
    foreach($node["children"] as $childNode){
      flattenNodeProps($childNode, $props);
    }
  }

  return $props;
}

