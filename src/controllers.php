<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Intervention\Image\ImageManager;
$app->match('/', function (Request $request) use ($app) {

    $form = $app['form.factory']
        ->createBuilder('form')
        ->add('FileUpload', 'file')
        ->getForm();

    $message = 'Upload a file';

    /**
     * @param $files
     * @param $path
     */
    function handleImageUpload($files, $path)
    {
        $fileUpload = $files['FileUpload'];
        $filename = $fileUpload->getClientOriginalName();

        $manager = new ImageManager(['driver' => 'gd']);
        $image = $manager
            ->make($fileUpload->getRealPath())
            ->rotate(-90)
            ->crop(1752, 250, 720, 1530);

        shell_exec(sprintf('rm -rf  %s/../../ocr-example/*.png', __DIR__));

        $croppedImage = $image->save(sprintf('%scropped_%s', $path, $filename), 100);
        shell_exec(sprintf('convert %s  -separate %simg_rgb_%%d.jpg', $croppedImage->basePath(), $path));
        $croppedImageRedChannelPath = sprintf('%simg_rgb_2.jpg', $path);
        splitInSections($path, $croppedImage, $manager->make($croppedImageRedChannelPath));
        $files['FileUpload']->move($path, $filename);
        shell_exec(sprintf('cp %s/../web/upload/*.png  %s/../../ocr-example', __DIR__, __DIR__));
        shell_exec(sprintf('chown www-data:richard %s/../../ocr-example/*.png', __DIR__));
        shell_exec(sprintf('chmod 777 %s/../../ocr-example/*.png', __DIR__));
    }

    /**
     * @param $path
     * @param $croppedImage
     * @param $croppedImageRedChannel
     */
    function splitInSections($path, $croppedImage, $croppedImageRedChannel)
    {
        $numberWidth = 140;
        $emptySpace = 148;
        $numberHeight = 230;
        $xStart = 0;
        // @todo fix part_5_clean to work with 5
        // @todo smaller red numbers in sperarate loop!
        for ($i = 1; $i < 8; $i +=1 ) {
            // third number has a weird spot on th right


            if ($i >= 5) {
                $numberWidth = 90;
                $numberHeight = 250;

            }

            /** @var \Intervention\Image\Image */
            $copy = determineCopyForUsage($croppedImage, $croppedImageRedChannel, $i);
            $yStart = 0;
            if ($i === 5) {
//                $copy->rotate(-5);
//                $yStart = 110;
//                $numberHeight = 238;
            }
            $part = $copy->crop($numberWidth, $numberHeight, $xStart, $yStart);



            $partPath = sprintf('%spart_%d.png', $path, $i);
            // -o 6 works with part 1 and 2
            $part->save($partPath, 100);
//    /* org*/     shell_exec(sprintf('sh %s/../textcleaner -g -e normalize -f 60 -o 6 -t 10 -p 0 %s %s', __DIR__, $partPath, sprintf('%spart_%d_clean.png', $path, $i)));
            if ($i === 6) {
                shell_exec(sprintf('sh %s/../textcleaner -u -g -e normalize -f 70 -o 6 -t 9 -p 0 -a 5 %s %s', __DIR__, $partPath, sprintf('%spart_%d_clean.png', $path, $i)));
            } elseif ($i === 5) {
                // @todo rotate the other way?
                shell_exec(sprintf('sh %s/../textcleaner -g -e normalize -f 70 -o 6 -t 9 -p 0 -a 5 %s %s', __DIR__, $partPath, sprintf('%spart_%d_clean.png', $path, $i)));
            } else {
                shell_exec(sprintf('sh %s/../textcleaner -g -e normalize -f 60 -o 6 -t 10 -p 0 %s %s', __DIR__, $partPath, sprintf('%spart_%d_clean.png', $path, $i)));
            }

            if ($i === 3) {
                $emptySpace = 165;
            }
            if ($i === 4) {
                $emptySpace = 100;
                $numberWidth = 140;

            }
            if ($i === 5) {
                $emptySpace = 140;
//                $numberWidth = 150;
            }
            $xStart += $numberWidth + $emptySpace;
        }
    }

    /**
     * @param $croppedImage
     * @param $croppedImageRedChannel
     * @param $i
     * @return mixed
     */
    function determineCopyForUsage($croppedImage, $croppedImageRedChannel, $i)
    {
        if ($i >= 5) {
            $copy = clone $croppedImageRedChannel;
            return $copy;
        } else {
            $copy = clone $croppedImage;
            return $copy;
        }
    }

    if ($request->isMethod('POST')) {

        $form->bind($request);

        if ($form->isValid()) {
            $files = $request->files->get($form->getName());
            /* Make sure that Upload Directory is properly configured and writable */
            $path = __DIR__ . '/../web/upload/';
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $fileUpload */
            handleImageUpload($files, $path);

            $message = 'File was successfully uploaded!';
        }
    }

    $response = $app['twig']->render(
        'index.html.twig',
        [
            'message' => $message,
            'form' => $form->createView()
        ]
    );

    return $response;

}, 'GET|POST');

$app->error(function (\Exception $e, $code) use ($app) {
    $response = null;

    if (!$app['debug']) {
        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }
        $response = new Response($message, $code);
    }

    return $response;
});
