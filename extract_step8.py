
import json

logfile = r"C:\Users\Fadla\.gemini\antigravity-ide\brain\e129b183-af9d-4db7-8e25-0381a09154a7\.system_generated\logs\transcript_full.jsonl"

step8_content = None
max_len = 0

with open(logfile, "r", encoding="utf-8") as f:
    for line in f:
        try:
            data = json.loads(line)
        except:
            continue
        
        step_index = data.get("step_index", 9999)
        if step_index > 2057:
            continue
            
        if data.get("type") == "PLANNER_RESPONSE":
            tool_calls = data.get("tool_calls", [])
            for tc in tool_calls:
                name = tc.get("name", "")
                if name in ["write_to_file", "replace_file_content", "multi_replace_file_content"]:
                    args = tc.get("args", {})
                    target = args.get("TargetFile", "")
                    if "Step8.vue" in target:
                        code = args.get("CodeContent", "")
                        if code and len(code) > max_len:
                            max_len = len(code)
                            step8_content = code
                            print(f"Found Step8 via {name} at step {step_index}, len={len(code)}")

if step8_content:
    with open("resources/js/Pages/owner/Asset/Create/Step8.vue", "w", encoding="utf-8") as f:
        f.write(step8_content)
    print("Written Step8.vue")
else:
    print("Not found via write_to_file. Searching rename/copy commands...")
    with open(logfile, "r", encoding="utf-8") as f:
        for line in f:
            try:
                data = json.loads(line)
            except:
                continue
            step_index = data.get("step_index", 9999)
            if step_index > 2057:
                continue
            if data.get("type") == "PLANNER_RESPONSE":
                tool_calls = data.get("tool_calls", [])
                for tc in tool_calls:
                    if tc.get("name") == "run_command":
                        args = tc.get("args", {})
                        cmd = args.get("CommandLine", "")
                        if "Step8" in cmd:
                            print(f"step={step_index}: {cmd[:300]}")

