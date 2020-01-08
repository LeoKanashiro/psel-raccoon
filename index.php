<?php
//Rota e variaveis utilizada para os itens A, B e C
$url = "https://us-central1-psel-clt-ti-junho-2019.cloudfunctions.net/psel_2019_get";
$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$postagens = json_decode(curl_exec($ch));

//Rota e variaveis utilizada para o item D
$url2 = "https://us-central1-psel-clt-ti-junho-2019.cloudfunctions.net/psel_2019_get_error";
$ch2 = curl_init($url2); 
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
$postagens2 = json_decode(curl_exec($ch2));

//Texto para o arquivo JSON
$texto = "www.github.com/LeoKanashiro/psel-raccoon";

//Vetores de armazenamento
$array = array();
$stack = array();
$array2 = array();
$stack2 = array();

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Postagens</title>
  </head>
  <body> 
      <p>{<br>
        'full_name': "Leonardo Kazuhiro Kanashiro",<br>
        'email': "leonardo.k.kanashiro@gmail.com",<br>
        'code_link': <?php echo $texto?>,<br>
        'response_a': [<br>

      <?php
      //Parte A - Pegar os produtos que contem promocao no titulo
      //Verifica se há promocao no titulo e armazena no vetor, o preco e o id do produto
      $vetor = array();

      if(count($postagens->posts)) {

      foreach($postagens->posts as $Postagens) {

        if(substr($Postagens->title, -8) == "promocao"){

          $vetor[$Postagens->price] = $Postagens->product_id;

        } 
       
      } }  else { ?>
        <strong>Nenhuma informacao retornada</strong>
      <?php }

      //ksort ordena pela chave
      ksort($vetor);
      $aux = 0;
        foreach($vetor as $key => $value){
          $valor = array();
          $valor = $value;

          $chave = array();
          $chave = $key;

          echo "{";
          echo '"product_id": ' . $value . ', "price_field: "' . $key;
          echo "},";
          echo "<br>";

          //Vetores que armazenam para o arquivo JSON, com array_push
          $array = ['product_id' => $valor, 'price_field' => $chave];
          array_push($stack, $array);
        }

        echo "],"
      ?>
      <br>'response_b': [<br> 
      <?php
      //Parte B - Pegar postagens com mais de 700 likes na mídia instagram_cpc
      $vetor2 = array();

      if(count($postagens->posts)) {

      foreach($postagens->posts as $Postagens) {
      
        if((($Postagens->media) == "instagram_cpc") && (($Postagens->likes) >= 700)){

          $vetor2[$Postagens->price] = $Postagens->post_id;
      
        } 
        
  
      } }  else { ?>
        <strong>Nenhuma informacao retornada</strong>
      <?php } ?>

      <?php
      ksort($vetor2);

      foreach($vetor2 as $key2 => $value2){
        echo '{"post_id": ' . $value2. ', "price_field": ' . $key2;
        echo "},";
        echo "<br>";

        //Vetores que armazenam para o arquivo JSON, com array_push
        $array2 = ['post_id' => $value2, 'price_field' => $key2];
        array_push($stack2, $array2);
      }

      echo "],";
    
      
      //Parte C - Somatório de likes do mes de maio nas midias google_cpc, facebook_cpc, instagram_cpc
      $soma = 0;
      if(count($postagens->posts)) {

      foreach($postagens->posts as $Postagens) {   
       
        if((($Postagens->media) == "google_cpc") || (($Postagens->media) == "facebook_cpc") || (($Postagens->media) == "instagram_cpc")){

          if(substr($Postagens->date, -7, 2) == 05){

            $soma = $soma + ($Postagens->likes);

          }
    
        } 
      
       
      } }  else { ?>
        <strong>Nenhuma informacao retornada</strong>
      <?php } ?>

      <br>'response_c': <?=$soma?>,
      <br>'response_d':[<br>

      <?php

      //Parte D - Verificar se há postagens do mesmo produto com precos diferentes e retornar o ID dos produtos
      //Faz a comparacao dos ids dos produtos e, se forem iguais, verifica o preco. Caso seja igual, nada acontece, mas se for diferente
      //faz adiciona ao vetor final, caso nao tenha esse produto ainda

      $vetor3 = array();
      $contador = 0;
      $teste = 0;
      $j = 0;
      if(count($postagens2->posts)) {
      
      foreach($postagens2->posts as $Postagens) {

      $compararProduto = $Postagens->product_id;
      $precoProduto = $Postagens->price;

        foreach($postagens2->posts as $Postagens2){
        $contador = 0;
        $j = 0;  
          if ($Postagens2->product_id == $compararProduto){
            if ($Postagens2->price != $precoProduto){

              
              while($j < count($vetor3)){
                if($vetor3[$j] == $compararProduto){
                  $contador = $contador + 1;
                  $j=$j+1;
                }else{
                  $j=$j+1;
                }
              }

              if($contador == 0){
                $vetor3[] = $compararProduto;
                $teste = count($vetor3);
              }
            }    
          }
        }    
       
      } }  else { ?>
        <strong>Nenhuma informacao retornada</strong>
      <?php }

        foreach($vetor3 as $value3){
          echo '"' . $value3 . '",';
        }

        echo "]";
        echo "<br>}";

        //Vetor em que é utilizado para criar o arquivo JSON
        $data = array(
            'full_name' => 'Leonardo Kazuhiro Kanashiro',
            'email' => 'leonardo.k.kanashiro@gmail.com',
            'code_link' => $texto,
            'response_a' => $stack,
            'response_b' => $stack2,
            'response_c' => $soma,
            'response_d' => $vetor3
        );

        //Cria o arquivo JSON
        $fp = fopen('resposta.json', 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);

      ?>

  </body>
</html>