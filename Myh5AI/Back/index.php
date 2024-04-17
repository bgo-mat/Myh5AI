<?php
include "./myClass.php";

$path = $_GET['path'] ?? '';
$h5ai = new H5AI($path);
$result = $h5ai->getTree();

function renderContent($item, $parentId = '') {
    $uniqueId = $parentId . htmlspecialchars($item['name']);
    $html = '<div class="dossier" id="' . $uniqueId . '">';
    $html .= '<p class="'.getFileIconClass($item).'">' . htmlspecialchars($item['name']) . '</p>';

    if ($item['type'] === "file") {
        $size = filesize($item['path']);
        $modified = filemtime($item['path']);
        $date = date("F d Y H:i:s.", $modified);
        $html .= '<p>Taille : ' . $size . ' octets</p>';
        $html .= '<p>Dernière modification le : ' . $date.'</p>';
        $html .= checkIfText($item);
    } elseif ($item['type'] === "directory") {
        $size = getDirectorySize($item['path']);
        $modified = filemtime($item['path']);
        $date = date("F d Y H:i:s.", $modified);
        $html .= '<p>Taille : ' . $size . formatSize($size).'</p>';
        $html .= '<p>Dernière modification le : ' . $date.'</p>';

        if (count($item['children']) > 0) {
            $html .= '<button class="voirPlusBtn" onclick="showMore(\'' . $uniqueId . '\')">➕</button>';
            $html .= '<div class="contenuDossier" style="display: none;">';

            foreach ($item['children'] as $child) {
                $html .= renderContent($child, $uniqueId . '_');
            }

            $html .= '</div>';
        }
    }

    $html .= '</div>';
    return $html;
}

function getDirectorySize($dirPath) {
    $totalSize = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isFile()) {
            $totalSize += $file->getSize();
        }
    }
    return $totalSize;
}


function formatSize($size) {
    if ($size < 1024) {
        return $size . ' B';
    } elseif ($size < 1048576) {
        return round($size / 1024, 2) . ' KB';
    } elseif ($size < 1073741824) {
        return round($size / 1048576, 2) . ' MB';
    } else {
        return round($size / 1073741824, 2) . ' GB';
    }
}



function getFileIconClass($item) {
    if ($item['type'] === "directory") {
        return "icon-directory";
    } else {
        $extension = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
        switch ($extension) {
            case 'pdf':
                return "icon-pdf";
            case 'jpg':
            case 'png':
            case 'gif':
                return "icon-image";
            case 'php':
                return "icon-php";
            case 'js':
                return "icon-js";
            case 'html':
                return "icon-html";
            case 'css':
                return "icon-css";
            case 'py':
                return "icon-py";
            default:
                return "icon-file";
        }
    }
}

function checkIfText($item) {
    if ($item['type'] === "directory") {
        return;
    } else {
        $extension = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
        $filName=$item['path'];
        switch ($extension) {
            case 'py':
            case 'css':
            case 'php':
            case 'js':
            case 'html':
            case 'txt':
                return '<button onclick="myReadFile(\'' . $filName . '\')">Afficher le contenu</button>';
            case 'jpg':
            case 'png':
                return '<button onclick="myReadImg(\'' . $filName . '\')">Afficher le contenu</button>';
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>H5AI</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
    <link rel="icon" type="svg" href="./assets/iconH5AI.svg" />
</head>
<body>

<div class="container">
    <?php
    foreach ($result as $item) {
        echo renderContent($item);
    } ?>
</div>

<div class="showContent">
    <button class="close">X</button><br><br>
    <p>Contenu de votre document : </p><br>
    <div class="content">
    </div>
</div>

<script>

    function myReadFile (input){
        document.getElementsByClassName("showContent")[0].style="display:block";
        fetch(input)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error');
                }
                return response.text();
            })
            .then(text => {
                document.querySelector('.content').innerText = text;
            });
    }

    function myReadImg (input){
        document.getElementsByClassName("showContent")[0].style="display:block";
        document.querySelector('.content').innerHTML = '<img src="' + input + '" alt="Image demandée" class="showImage">';
    }

    document.getElementsByClassName("close")[0].addEventListener("click",()=>{
        document.getElementsByClassName("showContent")[0].style="display:none";
    });

    function showMore(dossierId) {
        var contenuDossier = document.getElementById(dossierId).getElementsByClassName('contenuDossier')[0];
        var txtVoirPlus = document.getElementsByClassName('voirPlusBtn')[0];

        if (contenuDossier.style.display === 'none') {
            contenuDossier.style.display = 'block';
            txtVoirPlus.innerHTML="➖";
        } else {
            contenuDossier.style.display = 'none';
            txtVoirPlus.innerHTML="➕";
        }
    }
</script>

</body>
</html>