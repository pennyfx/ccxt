<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class therock extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'therock',
            'name' => 'TheRockTrading',
            'countries' => array ( 'MT' ),
            'rateLimit' => 1000,
            'version' => 'v1',
            'has' => array (
                'CORS' => false,
                'fetchTickers' => true,
                'fetchMyTrades' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766869-75057fa2-5ee9-11e7-9a6f-13e641fa4707.jpg',
                'api' => 'https://api.therocktrading.com',
                'www' => 'https://therocktrading.com',
                'doc' => array (
                    'https://api.therocktrading.com/doc/v1/index.html',
                    'https://api.therocktrading.com/doc/',
                ),
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'funds',
                        'funds/{id}/orderbook',
                        'funds/{id}/ticker',
                        'funds/{id}/trades',
                        'funds/tickers',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'balances',
                        'balances/{id}',
                        'discounts',
                        'discounts/{id}',
                        'funds',
                        'funds/{id}',
                        'funds/{id}/trades',
                        'funds/{fund_id}/orders',
                        'funds/{fund_id}/orders/{id}',
                        'funds/{fund_id}/position_balances',
                        'funds/{fund_id}/positions',
                        'funds/{fund_id}/positions/{id}',
                        'transactions',
                        'transactions/{id}',
                        'withdraw_limits/{id}',
                        'withdraw_limits',
                    ),
                    'post' => array (
                        'atms/withdraw',
                        'funds/{fund_id}/orders',
                    ),
                    'delete' => array (
                        'funds/{fund_id}/orders/{id}',
                        'funds/{fund_id}/orders/remove_all',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.2 / 100,
                    'taker' => 0.2 / 100,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'BTC' => 0.0005,
                        'BCH' => 0.0005,
                        'PPC' => 0.02,
                        'ETH' => 0.001,
                        'ZEC' => 0.001,
                        'LTC' => 0.002,
                        'EUR' => 2.5,  // worst-case scenario => https://therocktrading.com/en/pages/fees
                    ),
                    'deposit' => array (
                        'BTC' => 0,
                        'BCH' => 0,
                        'PPC' => 0,
                        'ETH' => 0,
                        'ZEC' => 0,
                        'LTC' => 0,
                        'EUR' => 0,
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetFunds ();
        //
        //     { funds => array ( array (                      $id =>   "BTCEUR",
        //                              description =>   "Trade Bitcoin with Euro",
        //                                     type =>   "currency",
        //                            base_currency =>   "EUR",
        //                           trade_currency =>   "BTC",
        //                                  $buy_fee =>    0.2,
        //                                 $sell_fee =>    0.2,
        //                      minimum_price_offer =>    0.01,
        //                   minimum_quantity_offer =>    0.0005,
        //                   base_currency_decimals =>    2,
        //                  trade_currency_decimals =>    4,
        //                                leverages => array ()                           ),
        //                {                      $id =>   "LTCEUR",
        //                              description =>   "Trade Litecoin with Euro",
        //                                     type =>   "currency",
        //                            base_currency =>   "EUR",
        //                           trade_currency =>   "LTC",
        //                                  $buy_fee =>    0.2,
        //                                 $sell_fee =>    0.2,
        //                      minimum_price_offer =>    0.01,
        //                   minimum_quantity_offer =>    0.01,
        //                   base_currency_decimals =>    2,
        //                  trade_currency_decimals =>    2,
        //                                leverages => array ()                            } ) }
        //
        $markets = $this->safe_value($response, 'funds');
        $result = array ();
        if ($markets === null) {
            throw new ExchangeError ($this->id . ' fetchMarkets got an unexpected response');
        } else {
            for ($i = 0; $i < count ($markets); $i++) {
                $market = $markets[$i];
                $id = $this->safe_string($market, 'id');
                $baseId = $this->safe_string($market, 'trade_currency');
                $quoteId = $this->safe_string($market, 'base_currency');
                $base = $this->common_currency_code($baseId);
                $quote = $this->common_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
                $buy_fee = $this->safe_float($market, 'buy_fee');
                $sell_fee = $this->safe_float($market, 'sell_fee');
                $taker = max ($buy_fee, $sell_fee);
                $taker = $taker / 100;
                $maker = $taker;
                $result[] = array (
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'info' => $market,
                    'active' => true,
                    'maker' => $maker,
                    'taker' => $taker,
                    'precision' => array (
                        'amount' => $this->safe_integer($market, 'trade_currency_decimals'),
                        'price' => $this->safe_integer($market, 'base_currency_decimals'),
                    ),
                    'limits' => array (
                        'amount' => array (
                            'min' => $this->safe_float($market, 'minimum_quantity_offer'),
                            'max' => null,
                        ),
                        'price' => array (
                            'min' => $this->safe_float($market, 'minimum_price_offer'),
                            'max' => null,
                        ),
                        'cost' => array (
                            'min' => null,
                            'max' => null,
                        ),
                    ),
                );
            }
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalances ();
        $balances = $response['balances'];
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = $balance['currency'];
            $free = $balance['trading_balance'];
            $total = $balance['balance'];
            $used = $total - $free;
            $account = array (
                'free' => $free,
                'used' => $used,
                'total' => $total,
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetFundsIdOrderbook (array_merge (array (
            'id' => $this->market_id($symbol),
        ), $params));
        $timestamp = $this->parse8601 ($orderbook['date']);
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'price', 'amount');
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->parse8601 ($ticker['date']);
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => $this->safe_float($ticker, 'close'), // previous day close, if any
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume_traded'),
            'quoteVolume' => $this->safe_float($ticker, 'volume'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetFundsTickers ($params);
        $tickers = $this->index_by($response['tickers'], 'fund_id');
        $ids = is_array ($tickers) ? array_keys ($tickers) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $ticker = $tickers[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetFundsIdTicker (array_merge (array (
            'id' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades
        //
        //     {      $id =>  4493548,
        //       fund_id => "ETHBTC",
        //        $amount =>  0.203,
        //         $price =>  0.02783576,
        //          $side => "buy",
        //          dark =>  false,
        //          date => "2018-11-30T08:19:18.236Z" }
        //
        // fetchMyTrades
        //
        //     {           $id =>    237338,
        //            fund_id =>   "BTCEUR",
        //             $amount =>    0.348,
        //              $price =>    348,
        //               $side =>   "sell",
        //               dark =>    false,
        //           order_id =>    14920648,
        //               date =>   "2015-06-03T00:49:49.000Z",
        //       $transactions => array ( array (       $id =>  2770768,
        //                             date => "2015-06-03T00:49:49.000Z",
        //                             type => "sold_currency_to_fund",
        //                            $price =>  121.1,
        //                         currency => "EUR"                       ),
        //                       array (       $id =>  2770769,
        //                             date => "2015-06-03T00:49:49.000Z",
        //                             type => "released_currency_to_fund",
        //                            $price =>  0.348,
        //                         currency => "BTC"                        ),
        //                       {       $id =>  2770772,
        //                             date => "2015-06-03T00:49:49.000Z",
        //                             type => "paid_commission",
        //                            $price =>  0.06,
        //                         currency => "EUR",
        //                         trade_id =>  440492                     }   ) }
        //
        if (!$market)
            $market = $this->markets_by_id[$trade['fund_id']];
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'date'));
        $id = $this->safe_string($trade, 'id');
        $orderId = $this->safe_string($trade, 'order_id');
        $side = $this->safe_string($trade, 'side');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        $fee = null;
        $feeCost = null;
        $transactions = $this->safe_value($trade, 'transactions', array ());
        $transactionsByType = $this->group_by($transactions, 'type');
        $feeTransactions = $this->safe_value($transactionsByType, 'paid_commission', array ());
        for ($i = 0; $i < count ($feeTransactions); $i++) {
            if ($feeCost === null) {
                $feeCost = 0;
            }
            $feeCost = $this->sum ($feeCost, $this->safe_float($feeTransactions[$i], 'price'));
        }
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $market['quote'],
            );
        }
        return array (
            'info' => $trade,
            'id' => $id,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired ($this->id . ' fetchMyTrades requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'id' => $market['id'],
        );
        if ($limit !== null) {
            $request['per_page'] = $limit; // default 25 max 200
        }
        if ($since !== null) {
            $request['after'] = $this->iso8601 ($since);
        }
        $response = $this->privateGetFundsIdTrades (array_merge ($request, $params));
        //
        //     { trades => array ( {           id =>    237338,
        //                        fund_id =>   "BTCEUR",
        //                         amount =>    0.348,
        //                          price =>    348,
        //                           side =>   "sell",
        //                           dark =>    false,
        //                       order_id =>    14920648,
        //                           date =>   "2015-06-03T00:49:49.000Z",
        //                   transactions => array ( array (       id =>  2770768,
        //                                         date => "2015-06-03T00:49:49.000Z",
        //                                         type => "sold_currency_to_fund",
        //                                        price =>  121.1,
        //                                     currency => "EUR"                       ),
        //                                   array (       id =>  2770769,
        //                                         date => "2015-06-03T00:49:49.000Z",
        //                                         type => "released_currency_to_fund",
        //                                        price =>  0.348,
        //                                     currency => "BTC"                        ),
        //                                   {       id =>  2770772,
        //                                         date => "2015-06-03T00:49:49.000Z",
        //                                         type => "paid_commission",
        //                                        price =>  0.06,
        //                                     currency => "EUR",
        //                                     trade_id =>  440492                     }   ) } ),
        //         meta => { total_count =>    31,
        //                       first => array ( href => "https://api.therocktrading.com/v1/funds/BTCXRP/trades?page=1" ),
        //                    previous =>    null,
        //                     current => array ( href => "https://api.therocktrading.com/v1/funds/BTCXRP/trades?page=1" ),
        //                        next => array ( href => "https://api.therocktrading.com/v1/funds/BTCXRP/trades?page=2" ),
        //                        last => array ( href => "https://api.therocktrading.com/v1/funds/BTCXRP/trades?page=2" )  } }
        //
        return $this->parse_trades($response['trades'], $market, $since, $limit);
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'id' => $market['id'],
        );
        if ($limit !== null) {
            $request['per_page'] = $limit; // default 25 max 200
        }
        if ($since !== null) {
            $request['after'] = $this->iso8601 ($since);
        }
        $response = $this->publicGetFundsIdTrades (array_merge ($request, $params));
        //
        //     { trades => array ( array (      id =>  4493548,
        //                   fund_id => "ETHBTC",
        //                    amount =>  0.203,
        //                     price =>  0.02783576,
        //                      side => "buy",
        //                      dark =>  false,
        //                      date => "2018-11-30T08:19:18.236Z" ),
        //                 {      id =>  4492926,
        //                   fund_id => "ETHBTC",
        //                    amount =>  0.04,
        //                     price =>  0.02767034,
        //                      side => "buy",
        //                      dark =>  false,
        //                      date => "2018-11-30T07:03:03.897Z" }  ),
        //         meta => { total_count =>    null,
        //                       first => array ( page =>  1,
        //                                href => "https://api.therocktrading.com/v1/funds/ETHBTC/trades?page=1" ),
        //                    previous =>    null,
        //                     current => array ( page =>  1,
        //                                href => "https://api.therocktrading.com/v1/funds/ETHBTC/trades?page=1" ),
        //                        next => array ( page =>  2,
        //                                href => "https://api.therocktrading.com/v1/funds/ETHBTC/trades?page=2" ),
        //                        last =>    null                                                                   } }
        //
        return $this->parse_trades($response['trades'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if ($type === 'market')
            $price = 0;
        $response = $this->privatePostFundsFundIdOrders (array_merge (array (
            'fund_id' => $this->market_id($symbol),
            'side' => $side,
            'amount' => $amount,
            'price' => $price,
        ), $params));
        return array (
            'info' => $response,
            'id' => (string) $response['id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        return $this->privateDeleteFundsFundIdOrdersId (array_merge (array (
            'id' => $id,
            'fund_id' => $this->market_id($symbol),
        ), $params));
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'active' => 'open',
            'executed' => 'closed',
            'deleted' => 'canceled',
            // don't know what this $status means
            // 'conditional' => '?',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = $nonce . $url;
            $headers = array (
                'X-TRT-KEY' => $this->apiKey,
                'X-TRT-NONCE' => $nonce,
                'X-TRT-SIGN' => $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha512'),
            );
            if ($query) {
                $body = $this->json ($query);
                $headers['Content-Type'] = 'application/json';
            }
        } else if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->rawencode ($query);
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('errors', $response))
            throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        return $response;
    }
}
