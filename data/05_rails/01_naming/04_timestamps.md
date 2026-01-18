---
title: タイムスタンプ
description: 作成日時・更新日時の自動管理
---

# タイムスタンプ

Railsでは、レコードの作成日時と更新日時を自動的に管理する仕組みが用意されています。

## 基本のタイムスタンプ

### created_at と updated_at

マイグレーションで `t.timestamps` を使用すると、2つのカラムが自動作成されます。

```ruby
# db/migrate/20240118000000_create_users.rb
class CreateUsers < ActiveRecord::Migration[7.0]
  def change
    create_table :users do |t|
      t.string :name
      t.string :email
      t.timestamps  # created_at, updated_at を追加
    end
  end
end
```

生成されるカラム：

| カラム名 | 型 | NULL許可 | 説明 |
|---------|-----|---------|------|
| `created_at` | `datetime` | NO | レコード作成日時 |
| `updated_at` | `datetime` | NO | レコード最終更新日時 |

## 自動更新の動作

### created_at

レコードが**最初に保存されたとき**に自動設定されます。

```ruby
user = User.new(name: "John")
user.created_at  # => nil

user.save
user.created_at  # => 2024-01-18 10:30:00

# 更新しても変わらない
user.update(name: "Jane")
user.created_at  # => 2024-01-18 10:30:00（変化なし）
```

### updated_at

レコードが**保存されるたび**に自動更新されます。

```ruby
user = User.create(name: "John")
user.created_at  # => 2024-01-18 10:30:00
user.updated_at  # => 2024-01-18 10:30:00

# 更新すると updated_at だけが変わる
user.update(name: "Jane")
user.created_at  # => 2024-01-18 10:30:00（変化なし）
user.updated_at  # => 2024-01-18 11:45:00（更新された）
```

## タイムスタンプを無効化

### モデル全体で無効化

```ruby
class User < ApplicationRecord
  self.record_timestamps = false
end
```

### 一時的に無効化

```ruby
User.record_timestamps = false
user.update(name: "John")  # updated_at が更新されない
User.record_timestamps = true
```

### ブロック内で無効化

```ruby
User.no_touching do
  user.update(name: "John")  # updated_at が更新されない
end
```

## touch メソッド

### 手動で updated_at を更新

```ruby
user = User.find(1)
user.touch
# updated_at が現在時刻に更新される
```

### 関連モデルのタイムスタンプを更新

```ruby
class Post < ApplicationRecord
  belongs_to :user, touch: true
end

post = Post.find(1)
post.update(title: "New Title")
# post の updated_at だけでなく、
# user の updated_at も自動更新される
```

### 任意のカラムを更新

```ruby
user.touch(:last_login_at)
# last_login_at と updated_at が現在時刻に更新される
```

## カスタムタイムスタンプ

### 論理削除用のタイムスタンプ

```ruby
# db/migrate/20240118000001_add_deleted_at_to_users.rb
class AddDeletedAtToUsers < ActiveRecord::Migration[7.0]
  def change
    add_column :users, :deleted_at, :datetime
  end
end
```

```ruby
class User < ApplicationRecord
  def soft_delete
    update(deleted_at: Time.current)
  end

  def self.active
    where(deleted_at: nil)
  end
end
```

### 公開日時

```ruby
# db/migrate/20240118000002_add_published_at_to_posts.rb
class AddPublishedAtToPosts < ActiveRecord::Migration[7.0]
  def change
    add_column :posts, :published_at, :datetime
  end
end
```

```ruby
class Post < ApplicationRecord
  scope :published, -> { where.not(published_at: nil) }
  scope :unpublished, -> { where(published_at: nil) }

  def publish!
    update(published_at: Time.current)
  end
end
```

## タイムゾーン

### Railsの時刻管理

Railsでは、データベースには**UTC**で保存され、アプリケーションでは**ローカル時刻**として扱われます。

```ruby
# config/application.rb
config.time_zone = 'Tokyo'  # アプリケーションのタイムゾーン
config.active_record.default_timezone = :utc  # DBへの保存形式
```

### 現在時刻の取得

```ruby
Time.current        # => Railsのタイムゾーン設定に従った現在時刻
Time.now            # => システムのタイムゾーンの現在時刻（非推奨）
DateTime.current    # => Time.current と同じ
```

推奨: `Time.current` を使用

## タイムスタンプの検索

### 作成日時で検索

```ruby
# 今日作成されたユーザー
User.where(created_at: Time.current.all_day)

# 過去7日間に作成されたユーザー
User.where(created_at: 7.days.ago..Time.current)

# 特定の日付に作成されたユーザー
User.where(created_at: Date.new(2024, 1, 18).all_day)
```

### 更新日時で検索

```ruby
# 最近更新されたユーザー
User.where("updated_at > ?", 1.hour.ago)

# 長期間更新されていないユーザー
User.where("updated_at < ?", 30.days.ago)
```

## timestamps の追加・削除

### 既存テーブルに追加

```ruby
# db/migrate/20240118000003_add_timestamps_to_users.rb
class AddTimestampsToUsers < ActiveRecord::Migration[7.0]
  def change
    add_timestamps :users, default: Time.current
  end
end
```

### タイムスタンプを削除

```ruby
# db/migrate/20240118000004_remove_timestamps_from_users.rb
class RemoveTimestampsFromUsers < ActiveRecord::Migration[7.0]
  def change
    remove_timestamps :users
  end
end
```

## NULL許可のタイムスタンプ

デフォルトではNULL不可ですが、変更可能です。

```ruby
class CreateDrafts < ActiveRecord::Migration[7.0]
  def change
    create_table :drafts do |t|
      t.string :title
      t.timestamps null: true  # NULL を許可
    end
  end
end
```

## まとめ

- `t.timestamps` で `created_at` と `updated_at` を自動追加
- `created_at` は作成時のみ設定
- `updated_at` は保存のたびに更新
- `touch: true` で関連モデルのタイムスタンプも更新
- データベースにはUTCで保存、アプリでローカル時刻として扱う
- `Time.current` を使って現在時刻を取得
