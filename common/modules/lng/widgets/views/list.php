<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <?=Yii::t('app', 'Languages')?> <span class="caret"></span>
    </a>

    <ul class="dropdown-menu">
        <?php
        foreach ($array_lang as $lang) {
            echo ' <li>' . $lang . '</li> ';
        }
        ?>
    </ul>

</li>
