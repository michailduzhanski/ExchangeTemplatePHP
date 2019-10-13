<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 10/15/2018
 * Time: 12:37 PM
 */
use yii\helpers\Url;
use \common\modules\drole\models\webtools\JSONRegistryFactory;
?>

<section class="pricing-page">
    <div class="container">
        <div class="center">  
            <h2><?=Yii::t('frontend', 'Pricing Table')?></h2>
            <p class="lead">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut <br> et dolore magna aliqua. Ut nim ad minim veniam</p>
        </div>  
        <div class="pricing-area text-center">
            <div class="row" id="list-pricing-table">
            </div>
        </div>
    </div>
</section>

<?php
$objectId = '6b6703fe-7ace-4e40-b1d3-2fd3dd30f65c';
$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectId, '');

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$url = Url::to(['/profile/default/add-coin']);
$js = <<<JS
    $.post('$apiRequestURL/drole/default/get-info', {json: JSON.stringify($json)}).done(function (data) {              
        var info = data['data']['data'];
        var structure = data['data']['structure']['data'];        
        var map = getStructureIDMapWithCheck(structure, null, null);                      
        var html = '';        
        
        var nameId = 'd4054ccc-d114-442f-9f27-bc445f31f20a';
        var priceId = 'f1e52a7d-fec8-450c-a483-57657f3cd18b';        
        
        var nameMap = map[nameId];
        var priceMap = map[priceId];
             
        $.each(info, function(index, value){        
            var name = value[nameMap[0]];
            var price = value[priceMap[0]];
            if(name !== null && price !== null)
                html += offer_template(name, price, structure, value, map);
        });
        $('#list-pricing-table').html(html);
    });
    
    function offer_template(name, price, structure, info, map){
        var featuredId =  'ca949171-00fb-4eb5-a89f-8dab5b3ebd45';
        var featuredMap = map[featuredId];
        var featured = info[featuredMap[0]];
        var id = '44bc1c31-f42a-4f30-b879-0c1125fcce03';
        var IdMap = map[id];
        var idItem = info[IdMap[0]];
        
        var exceptionIds = [
            'd4054ccc-d114-442f-9f27-bc445f31f20a', 'f1e52a7d-fec8-450c-a483-57657f3cd18b',
            'c8bbb7bb-d305-41a4-99dd-4eb62d5c9818', '1fdaf78f-23b0-4f97-afdb-195f6294633a',
            '44bc1c31-f42a-4f30-b879-0c1125fcce03', 'ca949171-00fb-4eb5-a89f-8dab5b3ebd45'
        ];
        
        var list = '';
        $.each(map, function(index, value){
            if(exceptionIds.indexOf(index) == -1){
                var data = info[value[0]];
                if(data !== null){            
                    list += '<li>' + info[value[0]] + '</li>';
                }
            }            
        });
        
        var plan = (featured) ? 'price-best' : 'price-normal';
        var heading = (featured) ? 'heading-best' : 'heading-normal';
        var sticker = (featured) ? '<img src="/images/ribon_one.png">' : '';
        
        return '<div class="col-sm-4 plan '+plan+' ">'+
        sticker + '<ul><li class="'+heading+'"><h1>'+name+'</h1>'+
        '<span>$'+price+'/Month</span></li>'+
        list +
        '<li class="plan-action">'+
        '<button onclick="location.href=\'$url?tariff='+idItem+'\'"  type="button" class="btn btn-primary">Buy</button>'+
        '</li></ul></div>';    
    }
JS;
$this->registerJs($js);
?>

