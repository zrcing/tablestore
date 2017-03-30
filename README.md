## Planfox Tablestore Component

The Planfox Tablestore Component is a aliyun tablestore toolkit for PHP, providing an expressive query builder, ActiveRecord style ORM, and schema builder.

### Usage Instructions

```
namespace App\TableStore;

use Planfox\Component\Tablestore\Model;
use Planfox\Component\Tablestore\ColumnTypeConst as TypeConst;

class User extends Model
{
    protected static $table = 'User';

    protected static $primaryKey = ['id' => TypeConst::CONST_INTEGER];

    protected static $fields = ['subscription'];
}
```
**Using The Query Builder**
```
$user = Users::find([12]);
$user->subscription = '23,455';
$user->save()

$user = Users::findOrNew([25]);
$user->subscription = '23,455';
$user->save()
```

Easy use aliyun tablestore


### License

The Planfox Tablestore Component is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

