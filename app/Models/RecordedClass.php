<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordedClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id',
        'title',
        'topic_name',
        'video_url',
        'thumbnail',
        'duration_minutes',
        'views_count',
        'summary',
    ];

    /**
     * URL that can go in an <iframe>. YouTube/Vimeo refuse to be framed on
     * their normal "watch" pages, so share links are turned into player
     * links. Null for anything that can't be framed (e.g. Zoom links).
     */
    public function embedUrl(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        // youtube.com/watch?v=ID, youtu.be/ID, /shorts/ID, /live/ID, /embed/ID
        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        // vimeo.com/123456 or player.vimeo.com/video/123456
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        // Google Drive (where Google Meet saves recordings):
        // drive.google.com/file/d/ID/view, /open?id=ID, /uc?id=ID -> /preview plays inline
        if (preg_match('~(?:drive|docs)\.google\.com/(?:file/d/|(?:open|uc)\?(?:.*&)?id=)([A-Za-z0-9_-]{10,})~', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/preview';
        }

        return null;
    }

    /**
     * Catches the links teachers commonly paste by mistake, with what to do
     * instead. Null when the link looks fine. Mirrored in the teacher
     * recordings form (linkCheck) for instant feedback.
     */
    public static function linkProblem(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $playable = (new self(['video_url' => $url]))->embedUrl() !== null;

        if (str_ends_with($host, 'meet.google.com')) {
            return 'That is the Google Meet link, not the recording. Find the recording in Google Drive → "Meet Recordings", then Share → Copy link.';
        }

        if (str_contains($url, '/folders/')) {
            return 'That is a Google Drive folder link. Open the recording video itself, then Share → Copy link.';
        }

        if (($host === 'drive.google.com' || $host === 'docs.google.com') && ! $playable) {
            return 'That Google Drive link does not point to a video. Open the recording itself, then Share → Copy link.';
        }

        if ((str_ends_with($host, 'youtube.com') || $host === 'youtu.be') && ! $playable) {
            return 'That YouTube link is not a video. Open the video and use Share → Copy.';
        }

        return null;
    }

    /** An uploaded or direct video file the <video> tag can play. */
    public function isDirectVideo(): bool
    {
        return (bool) preg_match('~\.(mp4|webm|mov|m4v|ogg)(\?.*)?$~i', (string) $this->video_url);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
