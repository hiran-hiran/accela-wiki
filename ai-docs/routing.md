# URL生成ルール

## 基本変換

ファイルパスからURLへの変換ルール：

```
data/{path}.md → /{url}
```

### 変換処理

1. `data/`プレフィックスを除去
2. `.md`拡張子を除去
3. 数字プレフィックス（`01_`, `02_`等）を除去
4. `index`の場合は親ディレクトリのパスに`/`を付与

## 変換例

| ファイルパス | URL |
|-------------|-----|
| `data/index.md` | `/` |
| `data/01_installation.md` | `/installation` |
| `data/02_guide/index.md` | `/guide/` |
| `data/02_guide/01_basics.md` | `/guide/basics` |
| `data/02_guide/02_sub/index.md` | `/guide/sub/` |
| `data/02_guide/02_sub/01_page.md` | `/guide/sub/page` |

## 末尾スラッシュのルール

| ファイル | URL | 末尾スラッシュ |
|----------|-----|---------------|
| `{name}.md` | `/{name}` | なし |
| `{dir}/index.md` | `/{dir}/` | あり |

## 数字プレフィックスの正規表現

```php
preg_replace('/^\d+_/', '', $name)
```

- `01_` → 除去
- `99_` → 除去
- `1_` → 除去
- `001_` → 除去
- `a01_` → 除去されない（数字で始まっていない）

## 注意事項

### URLに含めてはいけない文字

- スペース
- 日本語（技術的には可能だが非推奨）
- 大文字（小文字に統一推奨）
- アンダースコア（ケバブケース推奨）

### プレフィックス付きでアクセスした場合

`/01_installation` でアクセスすると404になります。
URLには常にプレフィックスなしでアクセスしてください。

## SSG（静的サイト生成）時のURL

`php app/bin/build`で静的出力した場合：

| 元ファイル | 出力ファイル |
|-----------|-------------|
| `data/index.md` | `out/index.html` |
| `data/01_installation.md` | `out/installation.html` |
| `data/02_guide/index.md` | `out/guide/index.html` |

Apache以外の環境（GitHub Pages等）で拡張子なしURLを使いたい場合は、
`{name}.md`ではなく`{name}/index.md`として作成してください。
