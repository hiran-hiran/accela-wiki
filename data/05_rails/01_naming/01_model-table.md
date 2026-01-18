---
title: モデルとテーブル
description: モデルクラス名とテーブル名の対応関係
---

# モデルとテーブル

Railsでは、モデルのクラス名とデータベースのテーブル名の対応関係が自動的に決定されます。

## 基本ルール

モデル名は**単数形**、テーブル名は**複数形**で命名します。

| モデルクラス名 | テーブル名 | 説明 |
|--------------|-----------|------|
| `User` | `users` | 規則的な複数形 |
| `Article` | `articles` | 規則的な複数形 |
| `Post` | `posts` | 規則的な複数形 |

```ruby
# app/models/user.rb
class User < ApplicationRecord
  # 自動的に users テーブルを参照
end
```

## 不規則な複数形

英語の不規則な複数形も自動的に変換されます。

| モデルクラス名 | テーブル名 | 説明 |
|--------------|-----------|------|
| `Person` | `people` | 不規則な複数形 |
| `Mouse` | `mice` | 不規則な複数形 |
| `Child` | `children` | 不規則な複数形 |
| `Ox` | `oxen` | 不規則な複数形 |

```ruby
class Person < ApplicationRecord
  # 自動的に people テーブルを参照
end
```

## 複合語の命名

複数の単語からなる名前の場合、以下のルールに従います。

| モデルクラス名 | テーブル名 | 説明 |
|--------------|-----------|------|
| `TodoItem` | `todo_items` | キャメルケース → スネークケース + 複数形 |
| `UserProfile` | `user_profiles` | キャメルケース → スネークケース + 複数形 |
| `BlogPost` | `blog_posts` | キャメルケース → スネークケース + 複数形 |

```ruby
# app/models/todo_item.rb
class TodoItem < ApplicationRecord
  # 自動的に todo_items テーブルを参照
end
```

### ファイル名もスネークケース

モデルのファイル名もスネークケースで命名します。

```
app/models/
├── user.rb           # User モデル
├── todo_item.rb      # TodoItem モデル
└── user_profile.rb   # UserProfile モデル
```

## テーブル名を明示的に指定

規約に従わない場合は、`table_name` で明示的に指定できます。

```ruby
class User < ApplicationRecord
  self.table_name = "members"  # users ではなく members テーブルを使用
end
```

ただし、規約に従う方が保守性が高く推奨されます。

## 単数形と複数形の変換ルール

Railsの `ActiveSupport::Inflector` が自動変換を行います。

### 規則的な変換

```ruby
"user".pluralize      # => "users"
"article".pluralize   # => "articles"
"box".pluralize       # => "boxes"
"quiz".pluralize      # => "quizzes"
"status".pluralize    # => "statuses"
```

### 不規則な変換

```ruby
"person".pluralize    # => "people"
"mouse".pluralize     # => "mice"
"child".pluralize     # => "children"
```

### 単数形への変換

```ruby
"users".singularize     # => "user"
"people".singularize    # => "person"
"mice".singularize      # => "mouse"
```

## カスタム変換ルール

独自の変換ルールを追加することも可能です。

```ruby
# config/initializers/inflections.rb
ActiveSupport::Inflector.inflections(:en) do |inflect|
  inflect.irregular 'octopus', 'octopi'
  inflect.uncountable %w[fish sheep]
end
```

## まとめ

- モデル名は単数形のキャメルケース（`TodoItem`）
- ファイル名は単数形のスネークケース（`todo_item.rb`）
- テーブル名は複数形のスネークケース（`todo_items`）
- 不規則な複数形も自動変換される
- 規約に従うことで設定不要
