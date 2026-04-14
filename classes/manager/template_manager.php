<?php

namespace local_rewards\manager;

use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Handles built-in badge templates and dynamic badge image rendering.
 */
class template_manager {
    /**
     * Returns the built-in bank badge templates installed with the plugin.
     *
     * @return array
     */
    public static function get_default_templates() {
        return [
            [
                "templatekey" => "completion_executive",
                "name" => "Certificado de Conclusão",
                "description" => "Liderança Executiva\n2024",
            ],
            [
                "templatekey" => "specialist_marketing",
                "name" => "Especialista Certificado",
                "description" => "Marketing Digital\nConcluído com Sucesso",
            ],
            [
                "templatekey" => "professional_projects",
                "name" => "Formação Profissional",
                "description" => "Gestão de Projetos\n2024",
            ],
            [
                "templatekey" => "performance_productivity",
                "name" => "Desempenho Excepcional",
                "description" => "Produtividade Máxima\nAtividade Concluída",
            ],
            [
                "templatekey" => "technical_web",
                "name" => "Capacitação Técnica",
                "description" => "Desenvolvimento Web\nCertificação 2024",
            ],
            [
                "templatekey" => "corporate_sales",
                "name" => "Treinamento Corporativo",
                "description" => "Habilidades de Vendas\nConcluído com Excelência",
            ],
        ];
    }

    /**
     * Ensures the default template badges exist in the badge bank.
     *
     * @return void
     */
    public static function install_default_badges() {
        global $DB;

        $now = time();

        foreach (self::get_default_templates() as $template) {
            $existing = $DB->get_record("local_rewards_badges", ["templatekey" => $template["templatekey"]]);
            if ($existing) {
                continue;
            }

            $record = (object) [
                "name" => $template["name"],
                "description" => $template["description"],
                "templatekey" => $template["templatekey"],
                "createdby" => 0,
                "timecreated" => $now,
                "timemodified" => $now,
            ];

            $DB->insert_record("local_rewards_badges", $record);
        }
    }

    /**
     * Returns whether a badge uses one of the built-in visual templates.
     *
     * @param stdClass|null $badge The badge record.
     * @return bool
     */
    public static function badge_has_template($badge) {
        return !empty($badge) && !empty($badge->templatekey);
    }

    /**
     * Returns the image URL for one template-based badge.
     *
     * @param stdClass $badge The badge record.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_badge_preview_url(stdClass $badge, $absolute = false) {
        $params = ["badgeid" => $badge->id];
        return new moodle_url("/local/rewards/badge_image.php", $params);
    }

    /**
     * Returns the image URL for one config using a built-in template.
     *
     * @param stdClass $config The config record.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_config_preview_url(stdClass $config, $absolute = false) {
        $params = ["configid" => $config->id];
        return new moodle_url("/local/rewards/badge_image.php", $params);
    }

    /**
     * Returns the image URL for one issued medal using a built-in template.
     *
     * @param stdClass $issue The issue record.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_issue_preview_url(stdClass $issue, $absolute = false) {
        $params = ["issueid" => $issue->id];
        return new moodle_url("/local/rewards/badge_image.php", $params);
    }

    /**
     * Renders one dynamic SVG badge image based on a built-in template.
     *
     * @param string $templatekey The template key.
     * @param string $name The main badge text.
     * @param string $description The secondary badge text.
     * @return string
     */
    public static function render_template_svg($templatekey, $name, $description) {
        global $CFG;

        $layout = self::get_template_layout($templatekey);
        if (!$layout) {
            return "";
        }

        $asseturl = "{$CFG->wwwroot}/local/rewards/pix/defaultbank/{$templatekey}.png";

        $svg = [];
        $svg[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="360" height="340" viewBox="0 0 360 340">';
        $svg[] = '<image href="' . $asseturl .
            '" x="0" y="0" width="360" height="340" preserveAspectRatio="xMidYMid meet"/>';
        $svg[] = '<text text-anchor="middle" y="220" x="180"
                        textLength="250" lengthAdjust="spacingAndGlyphs"
                        style="
                            font-size: 23px;
                            letter-spacing: -2px;
                            text-transform: uppercase;
                            font-weight: 700;
                            font-family: Arial, Helvetica, sans-serif;
                            fill: #111111;
                        ">' . $name . '</text>';

        $parts = explode("\n", $description);
        $svg[] = '<text text-anchor="middle" x="180" y="253" 
                        textLength="205" lengthAdjust="spacingAndGlyphs"
                        style="
                            font-size: 16px;
                            text-transform: uppercase;
                            font-weight: 700;
                            font-family: Arial, Helvetica, sans-serif;
                            fill: #ffffff;
                        ">' . $parts[0] . '</text>';
        if(isset($parts[1])){
            $svg[] = '<text text-anchor="middle" x="180" y="272" 
                            textLength="110" lengthAdjust="spacingAndGlyphs"
                            style="
                                font-size: 11px;
                                text-transform: uppercase;
                                font-weight: 400;
                                font-family: Arial, Helvetica, sans-serif;
                                fill: #ffffff;
                            ">' . $parts[1] . '</text>';
        }
        $svg[] = '</svg>';

        return implode("\n", $svg);
    }

    /**
     * Returns the color layout for one built-in template.
     *
     * @param string $templatekey The template key.
     * @return array|null
     */
    protected static function get_template_layout($templatekey) {
        $layouts = [
            "completion_executive" => [
                "topfill" => "#d9b561",
                "bottomfill" => "#123d7a",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
            "specialist_marketing" => [
                "topfill" => "#d7dde5",
                "bottomfill" => "#0b4b88",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
            "professional_projects" => [
                "topfill" => "#d3ae59",
                "bottomfill" => "#70757b",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
            "performance_productivity" => [
                "topfill" => "#d6a170",
                "bottomfill" => "#0f4b88",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
            "technical_web" => [
                "topfill" => "#d5dde4",
                "bottomfill" => "#7b8992",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
            "corporate_sales" => [
                "topfill" => "#d2ab55",
                "bottomfill" => "#10386f",
                "toptext" => "#111111",
                "bottomtext" => "#ffffff",
            ],
        ];

        return $layouts[$templatekey] ?? null;
    }

    /**
     * Splits a text into short lines suitable for the badge layout.
     *
     * @param string $text The source text.
     * @param int $maxchars Maximum characters per line.
     * @param int $maxlines Maximum number of lines.
     * @return array
     */
    protected static function split_text_lines($text, $maxchars, $maxlines) {
        $text = trim(str_replace(["\r\n", "\r"], "\n", strip_tags($text)));
        if ($text == "") {
            return [];
        }

        $rows = [];
        foreach (explode("\n", $text) as $paragraph) {
            $paragraph = trim(preg_replace('/s+/', ' ', $paragraph));
            if ($paragraph == "") {
                continue;
            }

            $words = explode(" ", $paragraph);
            $line = "";
            foreach ($words as $word) {
                $candidate = trim($line . " " . $word);
                if ($line != "" && strlen($candidate) > $maxchars) {
                    $rows[] = $line;
                    $line = $word;
                } else {
                    $line = $candidate;
                }

                if (count($rows) >= $maxlines) {
                    break 2;
                }
            }

            if ($line != "") {
                $rows[] = $line;
            }

            if (count($rows) >= $maxlines) {
                break;
            }
        }

        $rows = array_slice($rows, 0, $maxlines);
        $lastindex = count($rows) - 1;
        if ($lastindex >= 0 && strlen($rows[$lastindex]) > $maxchars) {
            $rows[$lastindex] = substr($rows[$lastindex], 0, $maxchars - 1) . "…";
        }

        return $rows;
    }
}
