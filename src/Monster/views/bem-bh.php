<?php
/** @var \BEM\BH $bh */
/** @var string $content */
Yii::beginProfile('BEM BH');
echo $bh->apply($content);
Yii::endProfile('BEM BH');