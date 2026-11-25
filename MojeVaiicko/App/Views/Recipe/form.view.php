<?php
/**
 * Formulár na pridanie / úpravu receptu.
 *
 * @var \App\Models\Recipe $recipe
 * @var array<int, array{amount: string, unit: string, name: string, shop_category: string}> $ingredients
 * @var array<int, array{title: string, description: string}> $steps
 * @var array<string, string> $errors
 * @var \Framework\Support\LinkGenerator $link
 */

use App\Helpers\Ui;
use App\Models\Recipe;

$isEdit = $recipe->getId() !== null;
Ui::setTitle($isEdit ? 'Upraviť recept' : 'Pridať recept');
$imageUrl = $isEdit ? Ui::imageUrl($recipe, $link) : null;

$err = fn(string $key) => isset($errors[$key])
        ? '<div class="field-error"><i class="bi bi-exclamation-circle"></i> ' . e($errors[$key]) . '</div>' : '';
$invalid = fn(string $key) => isset($errors[$key]) ? ' is-invalid' : '';
?>
<div class="page-container form-page">
    <nav class="crumbs" aria-label="Omrvinková navigácia">
        <a href="<?= $link->url('recipe.mine') ?>" class="crumbs-back"><i class="bi bi-arrow-left"></i> Moje recepty</a>
        <span>/</span>
        <span class="crumbs-current"><?= $isEdit ? e($recipe->getTitle()) : 'Nový recept' ?></span>
    </nav>

    <div class="page-head">
        <div>
            <h1><?= $isEdit ? 'Upraviť recept' : 'Pridať nový recept' ?></h1>
            <p><?= $isEdit ? 'Zmeny sa hneď prejavia všade, kde sa recept zobrazuje – aj v plánoch a obľúbených.'
                        : 'Po uložení sa recept zobrazí všetkým v sekcii Recepty a nájdeš ho v Moje recepty.' ?></p>
        </div>
    </div>

    <?php if ($errors !== []): ?>
        <div class="flash flash-danger mb-3" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <span>Recept sa nepodarilo uložiť – oprav označené polia.<?= !$isEdit || isset($errors['image']) ? ' Fotku bude potrebné vybrať znova.' : '' ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $link->url('recipe.save') ?>" enctype="multipart/form-data" class="recipe-form" novalidate data-recipe-form>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $recipe->getId() ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-main">
                <section class="card-box">
                    <h2>Základné informácie</h2>
                    <div class="mb-3">
                        <label for="title" class="form-label">Názov receptu *</label>
                        <input type="text" id="title" name="title" class="form-control<?= $invalid('title') ?>" maxlength="255" required
                               value="<?= e($recipe->getTitle()) ?>" placeholder="Napr. Cestoviny s paradajkami a špenátom">
                        <?= $err('title') ?>
                    </div>
                    <div class="mb-3">
                        <label for="summary" class="form-label">Krátky popis na kartu *</label>
                        <input type="text" id="summary" name="summary" class="form-control<?= $invalid('summary') ?>" maxlength="160" required
                               value="<?= e($recipe->getSummary()) ?>" placeholder="Jedna veta, ktorá navnadí – zobrazí sa v zozname receptov">
                        <?= $err('summary') ?>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Popis receptu</label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="Čo robí tento recept výnimočným?"><?= e($recipe->getDescription()) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="category" class="form-label">Kategória *</label>
                            <select id="category" name="category" class="form-select<?= $invalid('category') ?>" required>
                                <option value="" disabled <?= $recipe->getCategory() ? '' : 'selected' ?>>Vyber kategóriu</option>
                                <?php foreach (Recipe::CATEGORIES as $category): ?>
                                    <option value="<?= e($category) ?>" <?= $recipe->getCategory() === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('category') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="difficulty" class="form-label">Náročnosť *</label>
                            <select id="difficulty" name="difficulty" class="form-select<?= $invalid('difficulty') ?>" required>
                                <?php foreach (Recipe::DIFFICULTIES as $difficulty): ?>
                                    <option value="<?= e($difficulty) ?>" <?= $recipe->getDifficulty() === $difficulty ? 'selected' : '' ?>><?= e($difficulty) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('difficulty') ?>
                        </div>
                    </div>
                </section>

                <section class="card-box">
                    <div class="card-box-head">
                        <h2>Ingrediencie *</h2>
                        <small>Množstvo nechaj prázdne pre „podľa chuti“</small>
                    </div>
                    <div class="dyn-head ing-row-grid" aria-hidden="true">
                        <span>Množstvo</span><span>Jednotka</span><span>Surovina</span><span>Sekcia v nákupe</span><span></span>
                    </div>
                    <div class="dyn-list" data-dyn-list="ingredients">
                        <?php foreach ($ingredients as $i => $row): ?>
                            <div class="dyn-row ing-row-grid" data-dyn-row>
                                <input type="text" inputmode="decimal" class="form-control" name="ingredients[<?= $i ?>][amount]"
                                       value="<?= e($row['amount']) ?>" placeholder="200" aria-label="Množstvo">
                                <input type="text" class="form-control" name="ingredients[<?= $i ?>][unit]" list="unitOptions"
                                       value="<?= e($row['unit']) ?>" placeholder="g" maxlength="20" aria-label="Jednotka">
                                <input type="text" class="form-control" name="ingredients[<?= $i ?>][name]"
                                       value="<?= e($row['name']) ?>" placeholder="cherry paradajky" maxlength="120" aria-label="Surovina">
                                <select class="form-select" name="ingredients[<?= $i ?>][shop_category]" aria-label="Sekcia v nákupnom zozname">
                                    <?php foreach (Ui::SHOP_CATEGORIES as $code => $label): ?>
                                        <option value="<?= $code ?>" <?= $row['shop_category'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="icon-btn" data-dyn-remove aria-label="Odstrániť ingredienciu"><i class="bi bi-trash3"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?= $err('ingredients') ?>
                    <button type="button" class="btn btn-light-outline btn-sm mt-2" data-dyn-add="ingredients"><i class="bi bi-plus-lg"></i> Pridať ingredienciu</button>
                    <datalist id="unitOptions">
                        <?php foreach (Ui::UNITS as $unit): ?>
                            <option value="<?= e($unit) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </section>

                <section class="card-box">
                    <h2>Postup *</h2>
                    <div class="dyn-list" data-dyn-list="steps">
                        <?php foreach ($steps as $i => $row): ?>
                            <div class="dyn-row step-row" data-dyn-row>
                                <span class="step-num" data-step-number><?= $i + 1 ?></span>
                                <div class="step-fields">
                                    <input type="text" class="form-control" name="steps[<?= $i ?>][title]" maxlength="120"
                                           value="<?= e($row['title']) ?>" placeholder="Nadpis kroku, napr. Uvar cestoviny" aria-label="Nadpis kroku">
                                    <textarea class="form-control" name="steps[<?= $i ?>][description]" rows="2"
                                              placeholder="Čo presne treba urobiť?" aria-label="Popis kroku"><?= e($row['description']) ?></textarea>
                                </div>
                                <button type="button" class="icon-btn" data-dyn-remove aria-label="Odstrániť krok"><i class="bi bi-trash3"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?= $err('steps') ?>
                    <button type="button" class="btn btn-light-outline btn-sm mt-2" data-dyn-add="steps"><i class="bi bi-plus-lg"></i> Pridať krok</button>
                </section>

                <section class="card-box">
                    <h2>Malý tip proti plytvaniu</h2>
                    <textarea name="tip" class="form-control" rows="2"
                              placeholder="Ako využiť zvyšky? Ako dlho jedlo vydrží v chladničke?"><?= e($recipe->getTip()) ?></textarea>
                </section>
            </div>

            <div class="form-side">
                <section class="card-box">
                    <h2>Fotka</h2>
                    <div class="image-drop" data-image-preview>
                        <?php if ($imageUrl): ?>
                            <img src="<?= e($imageUrl) ?>" alt="Aktuálna fotka receptu">
                        <?php else: ?>
                            <div class="image-drop-empty"><i class="bi bi-image"></i><span>Zatiaľ bez fotky</span></div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="image" id="image" class="form-control mt-2<?= $invalid('image') ?>" accept="image/jpeg,image/png,image/webp">
                    <p class="form-text">JPG, PNG alebo WEBP do 5 MB. Najlepšie na šírku.</p>
                    <?= $err('image') ?>
                    <?php if ($imageUrl): ?>
                        <label class="check mt-1"><input type="checkbox" name="remove_image" value="1"> <span>Odstrániť fotku</span></label>
                    <?php endif; ?>
                </section>

                <section class="card-box">
                    <h2>Čas a porcie</h2>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="prep_time" class="form-label">Príprava (min)</label>
                            <input type="number" id="prep_time" name="prep_time" min="0" max="1440" class="form-control<?= $invalid('time') ?>"
                                   value="<?= e($recipe->getPrepTime()) ?>">
                        </div>
                        <div class="col-6">
                            <label for="cook_time" class="form-label">Varenie (min)</label>
                            <input type="number" id="cook_time" name="cook_time" min="0" max="1440" class="form-control<?= $invalid('time') ?>"
                                   value="<?= e($recipe->getCookTime()) ?>">
                        </div>
                    </div>
                    <?= $err('time') ?>
                    <label for="servings" class="form-label mt-2">Počet porcií</label>
                    <input type="number" id="servings" name="servings" min="1" max="20" class="form-control<?= $invalid('servings') ?>"
                           value="<?= e($recipe->getServings()) ?>">
                    <?= $err('servings') ?>
                </section>

                <section class="card-box">
                    <h2>Nutričné hodnoty <small class="text-muted fw-normal">na porciu</small></h2>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="calories" class="form-label">Kalórie (kcal) *</label>
                            <input type="number" id="calories" name="calories" min="1" max="5000" class="form-control<?= $invalid('nutrition') ?>"
                                   value="<?= e($recipe->getCalories()) ?>">
                        </div>
                        <div class="col-6">
                            <label for="protein" class="form-label">Bielkoviny (g)</label>
                            <input type="number" id="protein" name="protein" min="0" max="500" class="form-control" value="<?= e($recipe->getProtein()) ?>">
                        </div>
                        <div class="col-6">
                            <label for="carbs" class="form-label">Sacharidy (g)</label>
                            <input type="number" id="carbs" name="carbs" min="0" max="500" class="form-control" value="<?= e($recipe->getCarbs()) ?>">
                        </div>
                        <div class="col-6">
                            <label for="fat" class="form-label">Tuky (g)</label>
                            <input type="number" id="fat" name="fat" min="0" max="500" class="form-control" value="<?= e($recipe->getFat()) ?>">
                        </div>
                    </div>
                    <?= $err('nutrition') ?>
                </section>

                <section class="card-box">
                    <h2>Stravovanie</h2>
                    <div class="check-list">
                        <label class="check"><input type="checkbox" name="is_vegetarian" value="1" <?= $recipe->isVegetarian() ? 'checked' : '' ?>> <span>Vegetariánske</span></label>
                        <label class="check"><input type="checkbox" name="is_vegan" value="1" <?= $recipe->isVegan() ? 'checked' : '' ?>> <span>Vegánske</span></label>
                        <label class="check"><input type="checkbox" name="is_gluten_free" value="1" <?= $recipe->isGlutenFree() ? 'checked' : '' ?>> <span>Bez lepku</span></label>
                        <label class="check"><input type="checkbox" name="is_lactose_free" value="1" <?= $recipe->isLactoseFree() ? 'checked' : '' ?>> <span>Bez laktózy</span></label>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="btn btn-brand w-100"><i class="bi bi-check-lg"></i> <?= $isEdit ? 'Uložiť zmeny' : 'Zverejniť recept' ?></button>
                    <a href="<?= $isEdit ? $link->url('recipe.show', ['id' => $recipe->getId()]) : $link->url('recipe.mine') ?>"
                       class="btn btn-light-outline w-100">Zrušiť</a>
                </div>
            </div>
        </div>
    </form>

    <template id="tpl-ingredients">
        <div class="dyn-row ing-row-grid" data-dyn-row>
            <input type="text" inputmode="decimal" class="form-control" name="ingredients[__i__][amount]" placeholder="200" aria-label="Množstvo">
            <input type="text" class="form-control" name="ingredients[__i__][unit]" list="unitOptions" placeholder="g" maxlength="20" aria-label="Jednotka">
            <input type="text" class="form-control" name="ingredients[__i__][name]" placeholder="surovina" maxlength="120" aria-label="Surovina">
            <select class="form-select" name="ingredients[__i__][shop_category]" aria-label="Sekcia v nákupnom zozname">
                <?php foreach (Ui::SHOP_CATEGORIES as $code => $label): ?>
                    <option value="<?= $code ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="icon-btn" data-dyn-remove aria-label="Odstrániť ingredienciu"><i class="bi bi-trash3"></i></button>
        </div>
    </template>
    <template id="tpl-steps">
        <div class="dyn-row step-row" data-dyn-row>
            <span class="step-num" data-step-number></span>
            <div class="step-fields">
                <input type="text" class="form-control" name="steps[__i__][title]" maxlength="120" placeholder="Nadpis kroku" aria-label="Nadpis kroku">
                <textarea class="form-control" name="steps[__i__][description]" rows="2" placeholder="Čo presne treba urobiť?" aria-label="Popis kroku"></textarea>
            </div>
            <button type="button" class="icon-btn" data-dyn-remove aria-label="Odstrániť krok"><i class="bi bi-trash3"></i></button>
        </div>
    </template>
</div>
