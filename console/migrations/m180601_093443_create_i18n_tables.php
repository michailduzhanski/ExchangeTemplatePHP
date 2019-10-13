<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\db\Migration;


class m180601_093443_create_i18n_tables extends Migration
{
    public $lang_table = '{{%site_lang}}';

    public $lang_category_table = '{{%site_lang_category}}';

    public $lang_dictionary_table = '{{%site_lang_dictionary}}';

    public function SafeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema($this->lang_table);

        if($table !== null){
            $this->dropTable($this->lang_table);
        };

        $this->createTable($this->lang_table, [
            'code' => $this->string(3)->notNull(),
            'locale' => $this->string(),
            'name' => $this->string(),
            'default_lang' => $this->boolean()->defaultValue(false),
        ]);

        $this->batchInsert($this->lang_table, ['code', 'locale', 'name', 'default_lang'], [
            ['en', 'en-EN', 'English', 1],
            ['ru', 'ru-RU', 'Русский', 0],
            ['uk', 'uk-UA', 'Українська', 0]
        ]);

        $this->createTable($this->lang_category_table, [
           'code' => $this->string()->notNull(),
           'name' => $this->string()
        ]);
        $this->batchInsert($this->lang_category_table, ['code', 'name'], [
            ['app', 'Main dictionary'],
            ['frontend', 'Frontend'],
            ['backend', 'Backend']
        ]);


        $this->addPrimaryKey('pk_lang_code', $this->lang_table, 'code');
        $this->addPrimaryKey('pk_lc_code', $this->lang_category_table, 'code');

        $this->createTable($this->lang_dictionary_table, [
            'id' => $this->primaryKey(),
            'category' => $this->string(),
            'language' => $this->string(3),
            'text_group' => $this->integer(),
            'text' => $this->text()
        ]);

        $this->addForeignKey(
            'fk-lang_dictionary-category',
            $this->lang_dictionary_table,
            'category',
            $this->lang_category_table,
            'code',
            'CASCADE',
            'RESTRICT'
        );

        $this->createIndex(
            'idx-lang_dictionary-category',
            $this->lang_dictionary_table,
            'category'
        );

        $this->addForeignKey(
            'fk-lang_dictionary-language',
            $this->lang_dictionary_table,
            'language',
            $this->lang_table,
            'code',
            'CASCADE',
            'RESTRICT'
        );

        $this->createIndex(
            'idx-lang_dictionary-language',
            $this->lang_dictionary_table,
            'language'
        );
    }

    public function SafeDown()
    {
        $this->dropForeignKey('fk-lang_dictionary-language', $this->lang_dictionary_table);
        $this->dropForeignKey('fk-lang_dictionary-category', $this->lang_dictionary_table);
        $this->dropTable($this->lang_dictionary_table);
        $this->dropTable($this->lang_category_table);
        $this->dropTable($this->lang_table);
    }
}
