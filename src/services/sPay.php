<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sPay
{
    protected $aProof = '0x7a0b927A1f422411EA83733b81C680FAE01b4Fba';
    protected $apiUrl = 'https://dappcrypto.io';
    protected $dappUrl = 'https://dappcrypto.github.io';

    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct(){}

    public function getOrderData($aRecipient, $nOrder): array
    {
        try {
            $urlOrder = $this->apiUrl.'/dapp/api/proof/'.$aRecipient.'/'.$nOrder;

            $ch = curl_init($urlOrder);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,]);
            $orderDataJson = curl_exec($ch);
            if ($orderDataJson === false) { return ['error' => 1, 'data'  => curl_error($ch)]; }
            curl_close($ch);

            $orderData = json_decode($orderDataJson, true);
            if($orderData['error']!=0){
                return $orderData;
            }
            $checkProofData = $this->checkProof($orderData['aRecipient'], $orderData['nOrder'], $orderData['chain'], $orderData['sProof']);
            if($checkProofData['error']!=0){
                return $checkProofData;
            }

            $decode = sCoder::decodeObj($orderData['pData']);
            $pDataArr = '';
            if($decode['error']==0){
                $pDataArr = $decode['obj'];
            }
            $orderData['pDataArr'] = $pDataArr;

            return $orderData;
        } catch (\Exception $e) {
            return ['error'=> 1, 'data'=> 'exception: '.$e->getMessage()];
        }
    }

    public function getWebhook($aRecipient): array
    {
        try {
            $postData = $_REQUEST;
            if(empty($postData['aRecipient'])){
                return ['error'=> 1, 'data'=> 'No data'];
            }
            if($aRecipient!=$postData['aRecipient']){
                return ['error'=> 1, 'data'=> 'Error: aRecipient error; Proof is fake.'];
            }
            $aRecipient = $postData['aRecipient'];
            $nOrder = $postData['nOrder'];
            $chain = $postData['chain'];
            $sProof = $postData['sProof'];

            return $this->checkProof($aRecipient, $nOrder, $chain, $sProof);
        } catch (\Exception $e) {
            return ['error'=> 1, 'data'=> 'exception: '.$e->getMessage()];
        }
    }

    public function checkProof($aRecipient, $nOrder, $chain, $sProof): array
    {
        try {
            $bProof = sSign::instance()->getAbiCoderData(
                ['uint256[]', 'address[]'],
                [[(int)($chain),(int)($nOrder)],[$aRecipient]]
            );

            // Hash
            $bProofHash = sSign::instance()->getHashData($bProof);

            $signer = sEIP712::signer($bProofHash, $sProof);


            if(mb_strtolower($signer)!=mb_strtolower($this->aProof)){
                return ['error'=> 1, 'data'=> 'Error: Proof is fake'];
            }

            $orderData = [
                'aRecipient'=>$aRecipient,
                'nOrder'=>$nOrder,
                'chain'=>$chain,
            ];

            return ['error'=> 0, 'data'=> 'Success: The order was successfully paid.', 'orderData'=>$orderData];
        } catch (\Exception $e) {
            return ['error'=> 1, 'data'=> 'exception: '.$e->getMessage()];
        }
    }

    public function getAddressByPrivateKey(string $PrivateKey): array
    {
        try{
            $AddressAccount = sSecp256k1::addressFromPrivateKey($PrivateKey);
            return ['error'=> 0, 'data'=> 'Success', 'AddressAccount'=>$AddressAccount];
        } catch (\Exception $e) {
            return ['error'=> 1, 'data'=> 'exception: '.$e->getMessage()];
        }
    }

    public function getTextData(array $ArrData): array
    {
        $ToBase64 = sCoder::arrToBase64($ArrData);
        if($ToBase64['error']!=0){
            return ['error'=>1, 'data'=>'Error', 'base64String'=>"eyJzdWNjZXNzIjoiIiwiZXJyb3IiOiIiLCJyZXR1cm4iOiIiLCJEZXNjcmlwdGlvbiI6IiJ9"];
        }
        return ['error'=>0, 'data'=>'Success', 'base64String'=>$ToBase64['base64String']];
    }

    public function createInvoice(array $PDataObj, string $PrivateKey, $DefaultChainId = 1): array
    {
        try {

            $validatePData = sUtils::instance()->validatePData($PDataObj);
            if($validatePData['error']!=0){
                return $validatePData;
            }


            $AddressAccountData = $this->getAddressByPrivateKey($PrivateKey);
            if($AddressAccountData['error']!=0){
                return $AddressAccountData;
            }

            $OrderData = $this->getOrderData($PDataObj['aRecipient'],$PDataObj['nOrder']);
            if($OrderData['error']==0){
                return ['error'=> 1, 'data'=> 'Order: '.$PDataObj['nOrder'].' has already been paid in chain: '.$OrderData['chain']];
            }

            $PDataObj['nRToken'] = sUtils::instance()->floatToDecimals($PDataObj['nRToken'], 18);

            if(mb_strtolower($PDataObj['aRecipient'])!=mb_strtolower($AddressAccountData['AddressAccount'])){
                return ['error'=> 1, 'data'=> 'PrivateKey or aRecipient'];
            }

            $objPData = [
                'nArr' => [$PDataObj['nOrder'], $PDataObj['nRType'], $PDataObj['nRToken'], $PDataObj['start'], $PDataObj['deadline']],
                'aArr' => [$PDataObj['aRecipient'],$PDataObj['aRecipientExt']],
                'sArr' => [$PDataObj['sTextData']],
            ];

            $bPData = sSign::instance()->getAbiCoderData(
                ['uint256[]', 'address[]', 'string[]'],
                [$objPData['nArr'], $objPData['aArr'], $objPData['sArr']]
            );

            // Hash
            $bPDataHash = sSign::instance()->getHashData($bPData);

            $signature = sEIP712::sign($PrivateKey, $bPDataHash);
            $signer = sEIP712::signer($bPDataHash, $signature);
            if(mb_strtolower($AddressAccountData['AddressAccount']) != mb_strtolower($signer)){
                return ['error'=> 1, 'data'=> 'AddressAccount != signer'];
            }

            $ToObj = sCoder::base64ToObj($objPData['sArr'][0]);
            if($ToObj['error']!=0){ return ['error'=>1, 'data'=>'Text data error']; }
            $objPData['sArr'][0] = $ToObj['obj'];

            $InvoiceData = [
                'objPData' => $objPData,
                'signature' => $signature
            ];

            $encodeInvoiceDataRes = sCoder::encodeObj($InvoiceData);
            $enInvoice = $encodeInvoiceDataRes['b64url'];

            $LinkInvoice = $this->dappUrl.'/?dpage=CPayInvoice&chainid='.$DefaultChainId.'&enInvoice='.$enInvoice;

            return ['error'=> 0, 'data'=> 'Success', 'LinkInvoice'=>$LinkInvoice, 'aRecipient'=>$AddressAccountData['AddressAccount']];
        } catch (\Exception $e) {
            return ['error'=> 1, 'data'=> 'exception: '.$e->getMessage()];
        }
    }


}