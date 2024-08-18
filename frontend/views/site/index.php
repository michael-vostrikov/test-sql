<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'My Yii Application';

$endpointDescriptions = [
];
?>
<style>
    .endpoint-description td {
        padding: 4px;
        vertical-align: top;
        font-size: 14px;
    }
    .endpoint-description .get-params {
        display: inline-block;
        padding: 0;
        border: 0;
    }
    .endpoint-description .data, .endpoint-description .response {
        font-family: var(--bs-font-monospace);
        width: 30rem;
        height: 13rem;
    }
</style>
<div class="site-index">
    <?php foreach ($endpointDescriptions as $description) { ?>
        <div class="endpoint-description">
            <table>
                <tr>
                    <td>
                        <?php
                            echo Html::tag('span', $description['method'], ['class' => 'method']);
                            echo ' ';
                            echo Html::tag('span', $description['url'], ['class' => 'url']);
                            if ($description['get-params'] ?? false) {
                                echo '?';
                                echo Html::input('text', null, http_build_query($description['get-params']), ['class' => 'get-params']);
                            }
                            echo '<br>';

                            $value = isset($description['data'])
                                ? json_encode($description['data'], JSON_PRETTY_PRINT)
                                : '';
                            echo Html::textarea('', $value, ['class' => 'data']);
                        ?>
                    </td>
                    <td>
                        <br>
                        <button class="send">Send</button>
                    </td>
                    <td>
                        <span class="code"></span>
                        <br>
                        <textarea class="response"></textarea>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
</div>

<script>
    <?php ob_start(); ?>
    (function () {
      $('.endpoint-description .send').on('click', async function () {
        const parent = $(this).closest('.endpoint-description');

        const method = $('.method', parent).text();
        const url = $('.url', parent).text();
        const getParams = $('.get-params', parent).val();
        const data = $('.data', parent).val();

        const params = {
          method,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': $('[name="csrf-token"]').attr('content'),
          },
        };
        if (method !== 'GET' && method !== 'DELETE') {
          params.body = data;
        }

        $('.code', parent).html('');
        $('.response', parent).val('');

        let clientT1 = performance.now();
        await fetch(url + (getParams ? '?' + getParams : ''), params)
          .then(res => {
            let clientT2 = performance.now();
            $('.code', parent).html(res.status + ' ' + res.statusText + ' | client time (ms): ' + (clientT2 - clientT1).toFixed(0));
            return res.text();
          })
          .then(text => {
            let val;
            try {
              const json = JSON.parse(text);
              val = JSON.stringify(json, null, 4);
            } catch (error) {
              val = text;
            }

            $('.response', parent).val($('.response', parent).val() + val);
          });
      });
    })();
    <?php $this->registerJs(ob_get_clean()); ?>
</script>
