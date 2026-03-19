<!-- Template: 科技风格-变体7 (Content #1587) - AI/ML 流水线 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 顶部双线 -->
    <div class="absolute top-0 left-0 right-0 h-[2px]" style="background-color: #8B5CF6;"></div>
    <div class="absolute top-[4px] left-0 right-0 h-[1px]" style="background-color: rgba(139,92,246,0.3);"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="px-3 py-1" style="background-color: #8B5CF6;">
                <span class="font-mono text-[14px] font-bold text-white">AI/ML</span>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">机器学习流水线</h1>
                <p class="text-[13px] font-mono" style="color: #475569;">MACHINE LEARNING PIPELINE</p>
            </div>
        </div>
        <!-- Pipeline 步骤 -->
        <div class="flex gap-2 mb-5">
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #8B5CF6;">
                <p class="text-[13px] font-bold font-mono" style="color: #8B5CF6;">DATA</p>
                <p class="text-[12px]" style="color: #64748B;">数据采集</p>
            </div>
            <div class="flex items-center"><span class="font-mono text-[12px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #8B5CF6;">
                <p class="text-[13px] font-bold font-mono" style="color: #8B5CF6;">CLEAN</p>
                <p class="text-[12px]" style="color: #64748B;">数据清洗</p>
            </div>
            <div class="flex items-center"><span class="font-mono text-[12px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #06B6D4;">
                <p class="text-[13px] font-bold font-mono" style="color: #06B6D4;">FEATURE</p>
                <p class="text-[12px]" style="color: #64748B;">特征工程</p>
            </div>
            <div class="flex items-center"><span class="font-mono text-[12px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #06B6D4;">
                <p class="text-[13px] font-bold font-mono" style="color: #06B6D4;">TRAIN</p>
                <p class="text-[12px]" style="color: #64748B;">模型训练</p>
            </div>
            <div class="flex items-center"><span class="font-mono text-[12px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #3B82F6;">
                <p class="text-[13px] font-bold font-mono" style="color: #3B82F6;">EVAL</p>
                <p class="text-[12px]" style="color: #64748B;">模型评估</p>
            </div>
            <div class="flex items-center"><span class="font-mono text-[12px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-3 text-center" style="background-color: #1E293B; border-top: 3px solid #10B981;">
                <p class="text-[13px] font-bold font-mono" style="color: #10B981;">DEPLOY</p>
                <p class="text-[12px]" style="color: #64748B;">模型部署</p>
            </div>
        </div>
        <!-- 详细信息 -->
        <div class="flex-1 flex gap-5">
            <!-- 左侧：模型配置 -->
            <div class="flex-1 flex flex-col gap-4">
                <div class="p-5 font-mono" style="background-color: #1E293B;">
                    <p class="text-[14px] font-bold mb-3" style="color: #8B5CF6;">// Model Configuration</p>
                    <div class="space-y-1 text-[13px]">
                        <p><span style="color: #06B6D4;">model_type:</span> <span style="color: #94A3B8;">Transformer (LLM)</span></p>
                        <p><span style="color: #06B6D4;">parameters:</span> <span style="color: #94A3B8;">7B</span></p>
                        <p><span style="color: #06B6D4;">framework:</span> <span style="color: #94A3B8;">PyTorch 2.1</span></p>
                        <p><span style="color: #06B6D4;">training_data:</span> <span style="color: #94A3B8;">2.5TB</span></p>
                        <p><span style="color: #06B6D4;">optimizer:</span> <span style="color: #94A3B8;">AdamW</span></p>
                        <p><span style="color: #06B6D4;">learning_rate:</span> <span style="color: #94A3B8;">3e-4</span></p>
                        <p><span style="color: #06B6D4;">batch_size:</span> <span style="color: #94A3B8;">256</span></p>
                        <p><span style="color: #06B6D4;">epochs:</span> <span style="color: #94A3B8;">100</span></p>
                    </div>
                </div>
                <!-- 硬件配置 -->
                <div class="p-4 flex gap-3" style="background-color: #1E293B;">
                    <div class="flex-1 p-3" style="background-color: #0F172A;">
                        <p class="text-[12px] font-mono mb-1" style="color: #8B5CF6;">GPU</p>
                        <p class="text-[14px] font-bold text-white">8x A100</p>
                        <p class="text-[12px]" style="color: #475569;">80GB HBM</p>
                    </div>
                    <div class="flex-1 p-3" style="background-color: #0F172A;">
                        <p class="text-[12px] font-mono mb-1" style="color: #06B6D4;">RAM</p>
                        <p class="text-[14px] font-bold text-white">512GB</p>
                        <p class="text-[12px]" style="color: #475569;">DDR5</p>
                    </div>
                    <div class="flex-1 p-3" style="background-color: #0F172A;">
                        <p class="text-[12px] font-mono mb-1" style="color: #3B82F6;">Storage</p>
                        <p class="text-[14px] font-bold text-white">20TB</p>
                        <p class="text-[12px]" style="color: #475569;">NVMe SSD</p>
                    </div>
                </div>
            </div>
            <!-- 右侧：评估指标 -->
            <div class="w-[360px] flex-shrink-0 flex flex-col gap-4">
                <div class="p-5" style="background-color: #1E293B; border: 1px solid #8B5CF6;">
                    <p class="font-mono text-[13px] font-bold mb-3" style="color: #8B5CF6;">EVALUATION</p>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-[13px]" style="color: #94A3B8;">Accuracy</span><span class="font-mono text-[14px] font-bold text-white">96.8%</span></div>
                            <div class="h-1" style="background-color: #0F172A;"><div class="h-full" style="width: 96.8%; background-color: #10B981;"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-[13px]" style="color: #94A3B8;">Precision</span><span class="font-mono text-[14px] font-bold text-white">94.2%</span></div>
                            <div class="h-1" style="background-color: #0F172A;"><div class="h-full" style="width: 94.2%; background-color: #06B6D4;"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-[13px]" style="color: #94A3B8;">Recall</span><span class="font-mono text-[14px] font-bold text-white">92.5%</span></div>
                            <div class="h-1" style="background-color: #0F172A;"><div class="h-full" style="width: 92.5%; background-color: #3B82F6;"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-[13px]" style="color: #94A3B8;">F1-Score</span><span class="font-mono text-[14px] font-bold text-white">93.3%</span></div>
                            <div class="h-1" style="background-color: #0F172A;"><div class="h-full" style="width: 93.3%; background-color: #8B5CF6;"></div></div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 p-4" style="background-color: #1E293B;">
                    <p class="font-mono text-[13px] font-bold mb-2" style="color: #06B6D4;">TRAINING STATUS</p>
                    <div class="space-y-2">
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">训练时长</span><span class="text-[14px] font-mono text-white">72h</span></div>
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">当前 Epoch</span><span class="text-[14px] font-mono text-white">85/100</span></div>
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">Loss</span><span class="text-[14px] font-mono" style="color: #10B981;">0.0234</span></div>
                    </div>
                </div>
                <div class="p-3 font-mono" style="background-color: #1E293B;">
                    <p class="text-[12px]" style="color: #475569;">// MLflow + Kubeflow + Weights & Biases</p>
                </div>
            </div>
        </div>
    </div>
</div>