<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var string $templateFolder */
$this->setFrameMode(true);
$this->addExternalCss($templateFolder . '/style.css');

$rate = number_format((float)$arResult['CURRENCY']['RATE'], 2, '.', ' ');
?>
<div class="otus-currency-card">
	<div class="otus-currency-card__header">
		<div class="otus-currency-card__badge">
			<?= htmlspecialcharsbx($arResult['CURRENCY']['CODE']) ?>
		</div>
		<h2 class="otus-currency-card__title"><?= htmlspecialcharsbx(Loc::getMessage('OTUS_CURRENCIES_TEMPLATE_TITLE')) ?></h2>
	</div>

	<div class="otus-currency-card__grid">
		<div class="otus-currency-card__item">
			<div class="otus-currency-card__label"><?= htmlspecialcharsbx(Loc::getMessage('OTUS_CURRENCIES_TEMPLATE_RATE')) ?></div>
			<div class="otus-currency-card__value"><?= htmlspecialcharsbx($rate) ?></div>
		</div>

		<div class="otus-currency-card__item">
			<div class="otus-currency-card__label"><?= htmlspecialcharsbx(Loc::getMessage('OTUS_CURRENCIES_TEMPLATE_AMOUNT_CNT')) ?></div>
			<div class="otus-currency-card__value">
				<?= htmlspecialcharsbx((string)$arResult['CURRENCY']['AMOUNT_CNT']) ?>
			</div>
		</div>
	</div>
