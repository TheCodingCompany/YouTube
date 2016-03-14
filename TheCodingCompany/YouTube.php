<?php
namespace TheCodingCompany;

/**
 * Youtube video downloader
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2016, Victor Angelier
 * @link http://www.thecodingcompany The Coding Company
 * 
 * @example
 * 
 * $file = YouTube::downloadYTVideo()
 */

/**
 * Youtube PHP5 stream downloader
 */
class YouTubeClass
{
    
    /**
     * Force static use only
     */
    private function __construct() {}
    
    /**
     * Set the download location, seen from /app/
     * @var type 
     */
    protected static $download_location = "public"; //  Public or Storage
    
    /**
     * Define video format
     */
    private static $format = "video/mp4";
    
    /**
     * Define video quality
     */
    private static $quality = "medium";
    
    /**
     * YouTube get video info URL
     * @var type 
     */
    protected static $youtube_videoinfo_url = "https://www.youtube.com/get_video_info?video_id=%s&asv=1&el=detailpage&hl=en_US&el=vevo";
    
    /**
     * Video stream result list
     * @var type 
     */
    protected static $results = [];
    
    /**
     * Set download location
     */
    private static function download_location(){
        $location = public_path();
        switch(self::$download_location){
            case "public" :
                $location = public_path();
                break;
            case "storage" :
                $location = storage_path();
                break;
            default:
                $location = public_path();
                break;
        }
        return $location;
    }
    
    /**
     * Download youtube video
     * 
     * @param type $id
     */
    public static function downloadYTVideo($id = ""){
        
        //Get the list of streams
        $list = self::get_stream_list($id);
        
        foreach($list as $video){
            
            //We have the correct video
            if(self::matchesStreamQuality($video)){
                if(($file = self::download_stream($video)) !== FALSE){
                    return $file;
                }
            }
        }        
        return false;
    }
    
    /**
     * Download the video stream
     * @param type $stream_info
     * @return boolean
     */
    private static function download_stream($stream_info = []){
        //Set timelimit
        set_time_limit(0);
        
        //Open the video stream
        if(($video = @fopen($stream_info["url"], 'r')) !== FALSE){
            
            //Check our directory structure
            if(self::chk_storage()){
            
                //Open the file to write to
                if(($file = @fopen(self::download_location()."/videos/{$stream_info["video_id"]}.mp4", "w")) !== FALSE){

                    //Copy the video stream
                    stream_copy_to_stream($video, $file);
                    
                    //Close up
                    @fclose($video);
                    @fclose($file);
                    
                    unset($video);
                    unset($file);
                    
                    return public_path()."/videos/{$stream_info["video_id"]}.mp4";
                }else{
                    echo error_get_last();
                }
                
            }
        }else{
            echo "Error while downloading {$stream_info["url"]}<br/>";
            echo error_get_last();
        }
        return false;
    }
    
    /**
     * Check our storage path/directory
     * @return boolean
     */
    private static function chk_storage(){
        //If our directory does not exist. Create it
        if(!chdir(public_path()."/videos/")){
            if(mkdir(public_path()."/videos/", 0777) === FALSE){
                return false;
            }
        }
        return true;
    }
    
    /**
     * Matches our stream type
     * @param type $stream_info
     */
    private static function matchesStreamType($stream_info = []){
        return (stripos($stream_info["type"], self::$format) !== FALSE ? TRUE : FALSE);
    }
    
    /**
     * Matches our defined stream quality level
     * @param type $stream_info
     * @return type
     */
    private static function matchesStreamQuality($stream_info = []){
        return ($stream_info["quality"] === self::$quality);
    }
    
    /**
     * Create the video info URL
     * @param type $id
     * @return type
     */
    private static function url($id){
        return sprintf(self::$youtube_videoinfo_url, $id);
    }
    
    /**
     * Get streams by video id
     * @param type $id
     */
    private static function get_stream_list($id = ""){
        $url = self::url($id);
        $video_info = [];
        $stream_info = [];
        
        //Parse the string
        parse_str(file_get_contents($url), $video_info);
        
        if(isset($video_info['url_encoded_fmt_stream_map'])){
            foreach(explode(",", $video_info['url_encoded_fmt_stream_map']) as $url){
                
                parse_str($url, $stream_info);
                
                //Matching our stream type?
                if(self::matchesStreamType($stream_info)){
                    //Append the video id
                    $stream_info["video_id"] = $id;
                    self::add_video_stream($stream_info);
                }
            }
        }
        
        //Clean memory
        unset($video_info);
        unset($stream_info);
        
        return self::$results;
    }
    
    /**
     * Add video stream to our result set
     * @param type $stream_info
     */
    private static function add_video_stream($stream_info = []){
        //If we have a signature, add it to the Stream URL
        if(isset($stream_info['sig']) && $stream_info['sig'] !== ""){            
            $url = $stream_info['url'] . '&signature=' . $stream_info['sig'];            
        }elseif(isset($stream_info['s']) && $stream_info['s'] !== ""){ 
            $url = $stream_info['url'] . '&signature=' . $stream_info['s'];            
        }
        else{
            $url = $stream_info['url'];            
        }
        
        //Push to our results
        array_push(self::$results, [
            "url"         => $url,
            "video_id"    => $stream_info["video_id"],
            "quality"     => $stream_info["quality"]
        ]);
    }
}
