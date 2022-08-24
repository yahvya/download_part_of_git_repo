<?php

/** 
    @param owner
    @param repo
    @param path_list separted by comma . you can specify the dst by adding :dst_folder_path (a dst path have to end with '/')
    @param true to create the full path or false to put immediatly the data in the specified dst
    @param auth token if you need more call

    @errors missed parameter, max attempt, curl error
*/

function create_path(string $path):string
{

    $path_list = explode('/',$path);

    foreach($path_list as $key => $path)
    {
        if($key == 0)
            $previous_path = '';

        if
        (
            !is_dir($previous_path . $path) && 
            empty(explode('.',$path)[1]) 
        )
        {
            mkdir($previous_path . $path);

            echo "<chemin ({$previous_path}{$path}) crée>" . PHP_EOL;
        }

        $previous_path .= $path . '/'; 
    }

    return implode('/',$path_list);
}

if($argc >= 3)
{
    list(,$owner,$repo,$path_list,) = $argv;

    $include_full_path = !empty($argv[4]) ? $argv[4] : 'true';

    $curl = curl_init();

    if($curl)
    {
        $headers = [
            'accept: application/vnd.github+json',
            'User-Agent: ReqBin Curl Client/1.0',
        ];

        if(!empty($argv[5]) )
            array_push($headers,"Authorization: token {$argv[5]}");

        curl_setopt_array($curl,[
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);

        foreach(explode(',',$path_list) as $path)
        {
            $path_data = explode(':',$path);

            curl_setopt($curl,CURLOPT_URL,"https://api.github.com/repos/{$owner}/{$repo}/contents/{$path_data[0]}");

            $response = curl_exec($curl);

            if($response)
            {
                $response = json_decode($response,true);

                if(empty($response['name']) )
                {
                    // folder
                    
                    $filename = __FILE__;

                    foreach($response as $content)
                        echo shell_exec(!empty($path_data[1]) ? "php $filename $owner $repo {$content['path']}:{$path_data[1]} $include_full_path" : "php $filename $owner $repo {$content['path']} $include_full_path");
                }
                else
                {
                    // file type
                    if
                    (
                        $include_full_path != 'true' &&
                        @file_put_contents(!empty($path_data[1]) ? $path_data[1] . $response['name'] : $response['name'],base64_decode($response['content']) )
                    )   
                        echo "<Fichier ({$response['name']}) téléchargé>" . PHP_EOL;
                    else if(@file_put_contents(!empty($path_data[1]) ? create_path($path_data[1] . $path_data[0]) : create_path($path_data[0]),base64_decode($response['content']) ) )
                        echo "<Fichier ({$response['name']}) téléchargé>" . PHP_EOL;
                    else    
                        echo "Echec de téléchargement du fichier ({$response['name']})" . PHP_EOL;
                }
            }
            else echo "<Echec de la récupération pour le chemin({$path_data[0]})>" . PHP_EOL;
        }

        curl_close($curl);
    }
    else die('<Erreur interne>') . PHP_EOL;
}
else die('<Aucun lien trouvé>') . PHP_EOL;
