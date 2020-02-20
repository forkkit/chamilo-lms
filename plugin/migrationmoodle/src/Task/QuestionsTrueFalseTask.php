<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Task;

use Chamilo\PluginBundle\MigrationMoodle\Extractor\LoadedCoursesFilterExtractor;

/**
 * Class QuestionsTrueFalseTask.
 *
 * Task to convert Moodle question answers of truefalse type in Chamilo unique answers.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Task
 */
class QuestionsTrueFalseTask extends QuestionMultiChoiceSingleTask
{
    /**
     * @inheritDoc
     */
    public function getExtractConfiguration()
    {
        return [
            'class' => LoadedCoursesFilterExtractor::class,
            'query' => "SELECT
                    qa.id,
                    qa.question,
                    qa.answer,
                    qa.feedback,
                    (qa.fraction * qq.defaultmark) score,
                    q.id quizid,
                    q.course
                FROM mdl_question_answers qa
                INNER JOIN mdl_question qq ON qa.question = qq.id
                INNER JOIN mdl_quiz_slots qs ON qq.id = qs.questionid
                INNER JOIN mdl_quiz q ON qs.quizid = q.id
                WHERE qq.qtype = 'truefalse'",
        ];
    }
}
