<?php

namespace App\Services;

use App\Exports\WriteIntoExcel;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as Excel2;
use Maatwebsite\Excel\Facades\Excel;
use function env;

class FormatRecipeItemPropService {
    
    const ID = "ID";
    const RECIPE_SCHEMA_TYPE = "Recipe";
    const RECIPE_INDEX = 1;
    const URL_INDEX = 2;
    const RECIPE_URL_INDEX = "contentUrl";
    const PROPERTY_INDEX = "properties";
    const TOTAL_TIME = "totalTime";
    const TOTAL_TIME_ALIAS = "TotalTime";
    const DESCRIPTION = "description";
    const DESCRIPTION_ALIAS = "Description";
    const INGREDIENTS = "ingredients";
    const INGREDIENTS_ALIAS = "Ingredients";
    const NAME = "name";
    const NAME_ALIAS = "Name";
    const INSTRUCTIONS = "recipeInstructions";
    const INSTRUCTION_ALIAS = "Instructions";
    const TYPE = "type";
    const KEYWORDS = "keywords";
    const KEYWORDS_ALIAS = "Keywords";
    const PREP_TIME = "prepTime";
    const PREP_TIME_ALIAS = "PreparationTime";
    const COOK_TIME = "cookTime";
    const COOK_TIME_ALIAS = "CookTime";
    const CUISINE = "recipeCuisine";
    const CUISINE_ALIAS = "Cuisine";
    const IMAGE = "image";
    const IMAGE_ALIAS = "Image";
    const TO_IGNORE = [
        "recipe-in-hindi"
    ];
    
    public function __construct() {
        
    }
    
    public function formatAndWriteInExcel($folderPath, $endFileName = null, $title = null){
        $data = [];
        $files = $this->getAllFileNames($folderPath);
        if(empty($files)){
            throw new Exception("Empty folder");
        }
        $index = 1;
        foreach ($files as $fileName){
            $formattedData = $this->formatFileContent($fileName);
            if(!empty($formattedData)){
                $arrayWithId = [self::ID => $index];
                $data[] = array_merge($arrayWithId, $formattedData);
                $index++;
            }
        }
        if(empty($data)){
            throw new Exception("No valid file content");
        }
        $this->writeToExcel($endFileName, $title, $data);
    }

    public function getAllFileNames($folderPath){
        return File::allFiles($folderPath);
    }

    public function formatFileContent($fileName){
        $data = [];
        $contents = File::get($fileName);
        $contents = json_decode($contents, true);
        $data = $this->formatContent($contents);
        return $data;
    }
    
    private function formatContent($contentArr){
        $data = [];
        if(empty($contentArr)){
            return $data;
        }
        if(isset($contentArr[self::URL_INDEX][self::PROPERTY_INDEX][self::RECIPE_URL_INDEX])){
            foreach (self::TO_IGNORE as $ignore){
                Log::info("Ignore Values: ", [stripos($contentArr[self::URL_INDEX][self::PROPERTY_INDEX][self::RECIPE_URL_INDEX], $ignore), $contentArr[self::URL_INDEX][self::PROPERTY_INDEX][self::RECIPE_URL_INDEX], $ignore]);
                if(stripos($contentArr[self::URL_INDEX][self::PROPERTY_INDEX][self::RECIPE_URL_INDEX], $ignore) !== FALSE){
                    return $data;
                }
            }
        }
        if(!isset($contentArr[self::RECIPE_INDEX])){
            return $data;
        }
        if(!isset($contentArr[self::RECIPE_INDEX][self::TYPE])){
            return $data;
        }
        if(stripos($contentArr[self::RECIPE_INDEX][self::TYPE], self::RECIPE_SCHEMA_TYPE) !== false){
            $data = [
                self::NAME_ALIAS => isset($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::NAME]) ? (is_array($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::NAME]) ? implode(", ", $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::NAME]) : $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::NAME]) : '',
                self::DESCRIPTION_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::DESCRIPTION] ?? '',
                self::INGREDIENTS_ALIAS => isset($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INGREDIENTS]) ? (is_array($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INGREDIENTS])? json_encode($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INGREDIENTS]) : $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INGREDIENTS]) : '{}',
                self::INSTRUCTION_ALIAS => isset($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INSTRUCTIONS]) ? (is_array($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INSTRUCTIONS]) ? json_encode($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INSTRUCTIONS]) : $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::INSTRUCTIONS]) : '{}',
                self::KEYWORDS_ALIAS => isset($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::KEYWORDS]) ? (is_array($contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::KEYWORDS]) ? implode(", ", $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::KEYWORDS]) : $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::KEYWORDS]): '',
                self::CUISINE_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::CUISINE] ?? '',
                self::PREP_TIME_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::PREP_TIME] ?? '',
                self::COOK_TIME_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::COOK_TIME] ?? '',
                self::TOTAL_TIME_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::TOTAL_TIME] ?? '',
                self::IMAGE_ALIAS => $contentArr[self::RECIPE_INDEX][self::PROPERTY_INDEX][self::IMAGE] ?? '',
            ];
        }
        return $data;
    }
    
    public function writeToExcel($fileName, $title, $data){
        $headers = array_keys($data[0]);
        return Excel::store(new WriteIntoExcel($title, $data, $headers),  $fileName, 'local', Excel2::XLSX);
    }
}