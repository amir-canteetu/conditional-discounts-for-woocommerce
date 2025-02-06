<?php

class SurveyValidator {
    public function validate(array $data): bool {
        $schema = json_decode(file_get_contents('config/survey-schema.json'));
        return (new Validator())->validate($data, $schema);
    }
}

