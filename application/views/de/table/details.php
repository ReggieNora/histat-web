<div id="table_details" >
    <h1>Tabellenansicht der Studie</h1>
    <?= $project ?>


    <div class="details" id="tabelle">
        <div class="name"><?= $keymask->Name ?></div>


        <div id="scrollX" style="height:auto;overflow-y:hidden;overflow-x: auto;">
            <?= Form::open('table/details/' . $keymask->ID_HS . '#tabelle') ?>
            <table style="width:100%;" id="headline">
                <thead>
                    <?php $i = 0; ?>

                    <?php foreach ($details as $codeKurz => $detail) : ?>
                        <tr >

                            <td><div class="text">
                                    <?php
                                    $k = array_keys($detail);
                                    $beschreibung = $detail[$k[0]]->CodeBeschreibung;

                                    $filters[$codeKurz]["all"] = $beschreibung . ' *';

                                    $filters_reversed = array_reverse(Arr::get($filters, $codeKurz));
                                    ?>
                                    <?= Form::select('filter[]', $filters_reversed, Arr::get($post, $i, "all"), array('style' => 'width:100px')) ?></div></td>
                            <?php $i++ ?>
                            <?php foreach ($detail as $key => $value) : ?>
                                <td ><div class="text">
                                        <?php $str = substr($detail[$key]->CodeBezeichnung, 0, 30); ?>
                                        <?= (strlen($str) >= 30 ? $str . '... <div class="tooltip"><span></span>' . $detail[$key]->CodeBezeichnung . '</div>' : $str) ?>

                                    </div>
                                </td>


                            <?php endforeach; ?>

                        </tr>
                    <?php endforeach; ?>
                    <?php if ($data): ?>
                        <tr>
                            <td class="grey"><div class="text" style="height:auto">Tabelle</div></td>
                            <?php foreach ($keys as $key): ?>
                                <td class="grey"><div class="text" style="width:100%;text-align:center"><?= $tables[$key] ?></div></td>

                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="blue"><div class="text" style="height:auto">Grafik</div></td>
                            <?php foreach ($keys as $key): ?>
                                <td class="blue"><div class="text"  id="chart" style="width:100%;height:24px;text-align:center;margin:auto;padding:0;"><?= Form::hidden('title', implode('<br/>', $titles[$key])) ?> <?= Form::hidden('chart', $keymask->ID_HS . '/' . $key) ?><?= HTML::image($assets['img'] . 'layout/button-grafik.png') ?></div></td>

                            <?php endforeach; ?>
                        </tr>
                    <?php endif; ?>
                </thead>

            </table>
            <?= Form::close(); ?>

            <div id="scrollY" style="overflow:hidden;overflow-y:scroll;height:100px">
                <?php if ($data): ?>
                    <table>
                        <tbody>

                            <?php foreach ($data as $y => $data): ?>
                                <tr >

                                  
                                    <td><div class="text" style="height:auto"><?= $y ?></div></td>
                                    <?php foreach ($keys as $key): ?>
                                        <td ><div class="text" style="height:auto;text-align:center;margin:auto"><?= Arr::get($data, $key, '&nbsp;') ?></div></td>
                                      
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?> 
                        </tbody>
                    </table>
                <?php else: ?>
                <div style="position: fixed;left:50%;margin-top:50px;margin-left:-250px;width:300px" class="tooltip">
                   Die Studie <b><?= $keymask->Name ?></b> enthält <b><?= count($keys)?></b> Zeitreihen. <br/>Bitte verwenden Sie die Filtermöglichkeit um die Anzahl der Zeitreihen zu beschränken
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<div class="dialog"></div>

<script type="text/javascript">


    var closeText = "Schließen";

</script>
