<?php
// +----------------------------------------------------------------------
// | Project: shop
// +----------------------------------------------------------------------
// | Creation: 2022/8/2
// +----------------------------------------------------------------------
// | Filename: Maintain.php
// +----------------------------------------------------------------------
// | Explain: 网站维护模块「Pro版本程序基础功能模块」
// +----------------------------------------------------------------------
namespace extend;

use Medoo\DB\SQL;

class Maintain
{
    public static $DataTable = [];

    public static function databaseCalibre($type = 1)
    {
        if (!is_file(ROOT . 'install/Calibrating')) {
            return ['code' => -1, 'msg' => '程序数据校验文件不存在：' . ROOT . 'install/Calibrating'];
        }
        $dataSource = file_get_contents(ROOT . 'install/Calibrating');
        $dataSource = json_decode($dataSource, true);
        if (!$dataSource) {
            return ['code' => -1, 'msg' => '无法读取数据源[数据库校准文件]！，请检查数据源内容是否正确！'];
        }
        $latestDataStructure = self::databaseGenerate();
        $DB = SQL::DB();
        $numberSuccessCalibre_1 = 0;
        $numberSuccessCalibre_2 = 0;
        $numberCalibreFailure_1 = [];
        $numberCalibreFailure_2 = [];
        foreach ($dataSource['Structure'] as $key => $value) {
            $table = $key;
            $value[0][0] = str_replace("`{$key}`", "`{$table}`", $value[0][0]);
            $StandardSQL = $latestDataStructure[$key] ?? false;
            if ($type === 2) {
                $DB->query("DROP TABLE IF EXISTS `{$table}`;");
            }
            if (!$StandardSQL || $type === 2) {
                if ($DB->query(implode("\n", $value[0]))) {
                    ++$numberSuccessCalibre_1;
                } else {
                    $numberCalibreFailure_1[$table] = $DB->error();
                }
            } else {
                $totalNumberTableField = count($value[0]);
                $totalNumberCurrentTableField = count($latestDataStructure[$key][0]);
                $calibreTableFieldName = $value[0][$totalNumberTableField - 2];
                $currentTableFieldName = $latestDataStructure[$key][0][$totalNumberCurrentTableField - 2];
                if ($calibreTableFieldName !== $currentTableFieldName) {
                    if ($DB->query(trim("ALTER TABLE `{$table}` DROP PRIMARY KEY,ADD {$calibreTableFieldName}"))) {
                        ++$numberSuccessCalibre_1;
                    } else {
                        $numberCalibreFailure_1[$table . '_key'] = $DB->error();
                    }
                }
                $After = false;
                $statementExecute = $latestDataStructure[$key][1];
                $Kie = $statementExecute;
                $SortList = [];
                foreach ($value[1] as $k => $v) {
                    $SortList[$k] = $After ? " AFTER {$After} ;" : " FIRST ;";
                    if (!isset($statementExecute[$k])) {
                        $statementExecute2 = "ALTER TABLE `{$table}` ADD COLUMN " . rtrim($v, ",") . ($After ? " AFTER {$After} ;" : " FIRST ;");
                    } else {
                        if ($v !== $statementExecute[$k]) {
                            $statementExecute2 = "ALTER TABLE `{$table}` MODIFY COLUMN " . rtrim($v, ",");
                        } else {
                            $statementExecute2 = false;
                        }
                    }
                    if ($statementExecute2 && !isset($statementExecute[$k]) || trim($v) !== trim($statementExecute[$k])) {
                        if ($DB->query(trim($statementExecute2))) {
                            ++$numberSuccessCalibre_2;
                        } else {
                            $numberCalibreFailure_2[$table . '_' . $k] = $DB->error();
                        }
                        unset($statementExecute2);
                    }
                    $After = $k;
                    unset($Kie[$k]);
                }
                unset($After, $statementExecute);
                if (count($Kie) !== 0) {
                    $statementExecute3 = "ALTER TABLE `{$table}` ";
                    foreach ($Kie as $k => $v) {
                        $statementExecute3 .= "DROP COLUMN {$k} ,";
                    }
                    $statementExecute3 = rtrim($statementExecute3, ",") . ";";
                    if ($DB->query($statementExecute3)) {
                        ++$numberSuccessCalibre_1;
                    } else {
                        $numberCalibreFailure_1[$table . '_delete'] = $DB->error();
                    }
                    unset($statementExecute3, $Kie);
                }
                $statementExecute4 = "ALTER TABLE `{$table}` ";
                foreach ($SortList as $k => $v) {
                    $statementExecute4 .= "MODIFY COLUMN " . rtrim($value[1][$k], ",") . rtrim($v, ";") . ",";
                }
                $statementExecute4 = rtrim($statementExecute4, ",");
                if (!$DB->query($statementExecute4)) {
                    $numberCalibreFailure_1[$table . '_sort'] = $DB->error();
                }
                $classType = $latestDataStructure[$key][0][$totalNumberCurrentTableField - 1];
                if (!strstr($classType, 'InnoDB') || !strstr($classType, 'utf8mb4;') || !strstr($classType, 'utf8mb4_general_ci')) {
                    if (!$DB->query("ALTER TABLE `{$table}` ENGINE = InnoDB, CHARACTER SET = utf8mb4, COLLATE = utf8mb4_general_ci;")) {
                        $numberCalibreFailure_1[$table . '_engine'] = $DB->error();
                    }
                }
                unset($calibreTableFieldName, $currentTableFieldName, $totalNumberTableField, $totalNumberCurrentTableField, $SortList, $statementExecute4, $classType);
            }

            if (isset($dataSource['InstalData'][$table])) {
                $key = str_ireplace('sky_', '', $key);
                if (!$DB->count($key, [])) {
                    foreach ($dataSource['InstalData'][$table] as $v) {
                        if ($DB->query($v)) {
                            ++$numberSuccessCalibre_1;
                        } else {
                            $numberCalibreFailure_1[$table . '_import'] = $DB->error();
                        }
                    }
                }
            }
        }
        return ['code' => 1, 'msg' => '校准成功', 'data' => ['成功校准数据表的数量：' => $numberSuccessCalibre_1, '成功校准数据表字段的数量：' => $numberSuccessCalibre_2, '校准数据表失败的详情：' => $numberCalibreFailure_1, '成功校准数据表字段失败的详情：' => $numberCalibreFailure_2]];
    }

    public static function databaseGenerate()
    {
        global $dbconfig;
        $DB = SQL::DBS();
        $dbList = $DB->query("show tables");
        $Structure = [];
        while ($table = mysqli_fetch_assoc($dbList)) {
            if (empty($table['Tables_in_' . $dbconfig['dbname']])) {
                continue;
            }
            $table_name = $table['Tables_in_' . $dbconfig['dbname']];
            $Structure[$table_name] = self::databaseTable($table['Tables_in_' . $dbconfig['dbname']], $table_name);
        }
        return $Structure;
    }

    public static function databaseTable($table, $table_name)
    {
        $DB = SQL::DBS();
        $Res = $DB->query("show create table `{$table}`");
        $SQL = mysqli_fetch_assoc($Res)['Create Table'];
        $SQL = explode("\n", $SQL);
        $SQL[0] = str_replace($table, $table_name, $SQL[0]);
        $Ans = [];
        foreach ($SQL as $value) {
            if (strstr($value, 'CREATE TABLE') || strstr($value, 'PRIMARY KEY') || strstr($value, ') ENGINE=')) {
                continue;
            }
            $Ex = explode(" ", $value);
            $Ans[$Ex[2]] = $value;
            unset($Ex);
        }
        return [$SQL, $Ans];
    }
}