<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'My Yii Application';

$endpointDescriptions = [
    ['method' => 'POST', 'url' => '/order/create', 'data' => $data = [
        'external_id' => 'customerA',
        'order_num' => null,
        'order_date' => '2024-01-01',
        'order_details' => [
            ['sku' => 100001, 'price' => '100', 'qty' => 1],
            ['sku' => 100002, 'price' => '200', 'qty' => 2],
            ['sku' => 100003, 'price' => '300', 'qty' => 3],
            ['sku' => 100004, 'price' => '300', 'qty' => 3],
            ['sku' => 100005, 'price' => '300', 'qty' => 3],
            ['sku' => 100006, 'price' => '300', 'qty' => 3],
            ['sku' => 100007, 'price' => '300', 'qty' => 3],
            ['sku' => 100008, 'price' => '300', 'qty' => 3],
            ['sku' => 100009, 'price' => '300', 'qty' => 3],
            ['sku' => 100010, 'price' => '300', 'qty' => 3],
            ['sku' => 100011, 'price' => '300', 'qty' => 3],
            ['sku' => 100012, 'price' => '300', 'qty' => 3],
            ['sku' => 100013, 'price' => '300', 'qty' => 3],
            ['sku' => 100014, 'price' => '300', 'qty' => 3],
            ['sku' => 100015, 'price' => '300', 'qty' => 3],
            ['sku' => 100016, 'price' => '300', 'qty' => 3],
            ['sku' => 100017, 'price' => '300', 'qty' => 3],
            ['sku' => 100018, 'price' => '300', 'qty' => 3],
        ],
    ]],
    ['method' => 'POST', 'url' => '/order/create-with-procedure', 'data' => $data],
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

    <br>
    <br>
    <br>
    <br>
    <button class="send-100-requests">Send 100 requests</button>
    <br>
    <br>
    <div class="stat-100-requests" style="min-height: 100px"></div>
    <br>
    <br>
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

<script>
    <?php ob_start(); ?>
    $('.send-100-requests').click(async () => {

      const orderData = {
        "external_id": "testCustomer",
        "order_num": null,
        "order_date": "2024-01-01",
        "order_details": [],
      };

      const orderLinesNumber = 100;
      for (let i = 1; i <= orderLinesNumber; i++) {
        orderData.order_details.push({
          "sku": 100000 + i,
          "price": "100",
          "qty": Math.floor(Math.random() * 3),
        });
      }

      const params = {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-Token': $('[name="csrf-token"]').attr('content'),
        },
        body: JSON.stringify(orderData),
      };

      const url1 = '/order/create';
      const url2 = '/order/create-with-procedure';

      let [avgServerTime1, avgClientTime1] = await performRequests(url1, params);
      let [avgServerTime2, avgClientTime2] = await performRequests(url2, params);

      let html = ''
        + 'Average server time (ms):' + '<br>'
        + 'APP: ' + avgServerTime1.toFixed(0) + '<br>'
        + 'SQL: ' + avgServerTime2.toFixed(0) + '<br>'
        + 'Average client time: (ms)' + '<br>'
        + 'APP: ' + avgClientTime1.toFixed(0) + '<br>'
        + 'SQL: ' + avgClientTime2.toFixed(0) + '<br>'
      ;
      $('.stat-100-requests').html(html);
    });

    async function performRequests(url, params) {
      const requestNumber = 100;
      const parallelRequestNumber = 4;
      let promiseList = [];

      const requestStat = {serverTime: [], clientTime: []};

      let completedNumber = 0;
      for (let i = 1; i <= requestNumber; i++) {
        let clientT1 = performance.now();

        const request = fetch(url, params)
          .then(res => res.text())
          .then(text => {
            const clientT2 = performance.now();
            const clientTime = clientT2 - clientT1;
            requestStat.clientTime.push(clientTime);

            completedNumber++;
            $('.stat-100-requests').html('url: ' + url + ', completed: ' + completedNumber);

            try {
              const json = JSON.parse(text);
              requestStat.serverTime.push(json.serverTime);
            } catch (error) {
              requestStat.serverTime.push(1000);
              console.log(error, text);
            }
          });

        promiseList.push(request);
        if (i % parallelRequestNumber === 0) {
          await Promise.all(promiseList);
          promiseList = [];
        }
      }
      await Promise.all(promiseList);

      console.log(requestStat);

      const avgServerTime = requestStat.serverTime.reduce((a, e) => a + e, 0) / requestStat.serverTime.length;
      const avgClientTime = requestStat.clientTime.reduce((a, e) => a + e, 0) / requestStat.clientTime.length;

      return [avgServerTime, avgClientTime];
    }
    <?php $this->registerJs(ob_get_clean()); ?>
</script>
