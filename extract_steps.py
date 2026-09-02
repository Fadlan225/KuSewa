
import json
import re

logfile = r"C:\Users\Fadla\.gemini\antigravity-ide\brain\e129b183-af9d-4db7-8e25-0381a09154a7\.system_generated\logs\transcript_full.jsonl"

# We need to find write_to_file calls for Step8.vue, Step9.vue, Step10.vue
# that happened BEFORE the refactoring of Index.vue (before line ~2057 which was the gemuk request)
files_found = {}

with open(logfile, "r", encoding="utf-8") as f:
    for line in f:
        try:
            data = json.loads(line)
        except:
            continue
        
        step_index = data.get("step_index", 9999)
        # Only look at steps before the "masih terlalu gemuk" request (step_index ~2057)
        if step_index > 2057:
            continue
            
        if data.get("type") == "PLANNER_RESPONSE":
            tool_calls = data.get("tool_calls", [])
            for tc in tool_calls:
                if tc.get("name") == "write_to_file":
                    args = tc.get("args", {})
                    target = args.get("TargetFile", "")
                    for step_name in ["Step8.vue", "Step9.vue", "Step10.vue"]:
                        if step_name in target:
                            code = args.get("CodeContent", "")
                            if code and (step_name not in files_found or len(code) > len(files_found[step_name])):
                                files_found[step_name] = code
                                print(f"Found {step_name} at step_index {step_index}, len={len(code)}")

for fname, content in files_found.items():
    out_path = f"resources/js/Pages/owner/Asset/Create/{fname}"
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(content)
    print(f"Written: {out_path}")

