<?php

function sync_rate_plans($plans) {

    if (!empty($plans)) {
        process_rate_plans($plans);
        log_event("INFO", count($plans) . " rate plans synced.");
    }

}

function process_rate_plans($plans) {
    foreach ($plans as $plan) {

        $code = "RP-" . $plan["id_pricing_plans"] . "-" . $plan["name"];

        upsert_rate_plan(
            $plan["id_pricing_plans"],
            $code,
            $plan["name"]
        );
    }
}

function upsert_rate_plan($hs_id, $code, $name) {

    db_upsert(
        "rate_plans",
        [
            "hs_rate_plan_id" => $hs_id,
            "code" => $code,
            "meal_plan" => $name
        ],
        "hs_rate_plan_id"
    );
}

?>