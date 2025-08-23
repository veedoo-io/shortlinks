<?php

namespace App\Service\Audio\Gemini;

use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;

class GeminiMp3Service
{
    private Filesystem $diskSpace;

    private Filesystem $diskLocal;

    private string $pathPcm;
    private string $pathMp3;

    public function __construct()
    {
        $this->diskLocal = Storage::disk('local');
    }

    /**
     * Select disk space
     *
     * @param string $referrer
     *
     * @return Filesystem
     */
    public function selectDiskSpace(string $referrer): Filesystem
    {
        $diskName = str_contains($referrer, 'preprod') ? 's3-preprod' : 's3';

        $this->diskSpace = Storage::disk($diskName);

        return $this->diskSpace;
    }

    /**
     * Generate file name
     *
     * @param string $fileName
     *
     * @return array
     */
    public function generateFileName(string $fileName)
    {
        $namePcm = "{$fileName}.pcm";
        $nameMp3 = "{$fileName}.mp3";

        $this->pathPcm = "audio/gemini/{$namePcm}";
        $this->pathMp3 = "audio/gemini/{$nameMp3}";

        return [
            $namePcm,
            $nameMp3,
            $this->pathPcm,
            $this->pathMp3,
        ];
    }

    /**
     * Execute the ffmpeg command
     *
     * @throws Exception
     */
    public function execConvertCommand(): bool
    {
        $realPathPcm = escapeshellarg($this->diskLocal->path($this->pathPcm));
        $realPathMp3 = escapeshellarg($this->diskLocal->path($this->pathMp3));

        $resultCommand = \exec(sprintf("ffmpeg -f s16le -ar 24000 -ac 1 -i %s %s", $realPathPcm, $realPathMp3), $output, $returnVar);

        // is Not Successful command
        if ($returnVar !== 0) {
            Log::error('ffmpeg failed', [
                'exit_code' => $returnVar,
                'error_output' => $output,
                'command' => $resultCommand,
            ]);

            throw new Exception(json_encode($output));
        }

        return true;
    }

    /**
     * Delete the temporary files
     *
     * @return void
     */
    public function cleanup(): void
    {
        $this->diskLocal->delete($this->pathPcm);
        $this->diskLocal->delete($this->pathMp3);
    }

    /**
     * Check if the converted file exists
     *
     * @return void
     * @throws Exception
     */
    public function existsConvertedFile(): void
    {
        if (!$this->diskLocal->exists($this->pathMp3)) {
            throw new Exception('MP3 file not generated');
        }
    }

    /**
     * Store file on space
     *
     * @param string $path
     *
     * @return bool
     * @throws Exception
     */
    public function storeFileOnSpace(string $path): bool
    {
        $this->diskSpace->delete($path);
        $stream = $this->diskLocal->readStream($this->pathMp3);

        if ($stream === false) {
            throw new Exception('Failed to open local MP3 stream');
        }

        $resultSave = $this->diskSpace->put($path, $stream, ['visibility' => Visibility::PUBLIC]);
        $this->diskSpace->setVisibility($path, Visibility::PUBLIC);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (!$resultSave) {
            throw new Exception('Failed to save MP3 file');
        }

        return true;
    }

    /**
     * Store as audio
     *
     * @param UploadedFile $fileGeminiPcm
     *
     * @return bool
     * @throws Exception
     */
    public function storeAsAudio(UploadedFile $fileGeminiPcm): bool
    {
        $result = $fileGeminiPcm->storeAs('audio/gemini', basename($this->pathPcm), ['disk' => 'local']);

        if (!$result) {
            throw new Exception('Failed to save PCM file');
        }

        return true;
    }
}