<!-- Template: 科技风-神经网络 (Transition #561) -->
            <div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-[#0a0e1a]">
                <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

                    <!-- 微点阵背景 -->
                    <svg class="absolute inset-0 w-full h-full opacity-[0.05]" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="t561-micro" width="30" height="30" patternUnits="userSpaceOnUse">
                            <circle cx="15" cy="15" r="0.6" fill="#a78bfa"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#t561-micro)"/>
                    </svg>

                    <!-- 神经网络连接图 -->
                    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <!-- Layer 1 (左侧输入层) 节点位置: x=160 -->
                        <!-- Layer 2 (隐藏层1) 节点位置: x=380 -->
                        <!-- Layer 3 (中央) 节点位置: x=675 (中心) -->
                        <!-- Layer 4 (隐藏层2) 节点位置: x=970 -->
                        <!-- Layer 5 (右侧输出层) 节点位置: x=1190 -->

                        <!-- L1 -> L2 连线 -->
                        <line x1="160" y1="160" x2="380" y2="200" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="160" y1="160" x2="380" y2="320" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="160" y1="300" x2="380" y2="200" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="160" y1="300" x2="380" y2="320" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="160" y1="300" x2="380" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.12"/>
                        <line x1="160" y1="440" x2="380" y2="320" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="160" y1="440" x2="380" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="160" y1="440" x2="380" y2="560" stroke="#7c3aed" stroke-width="0.6" opacity="0.12"/>
                        <line x1="160" y1="580" x2="380" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="160" y1="580" x2="380" y2="560" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>

                        <!-- L4 -> L5 连线 -->
                        <line x1="970" y1="200" x2="1190" y2="160" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="970" y1="200" x2="1190" y2="300" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="970" y1="320" x2="1190" y2="300" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="970" y1="320" x2="1190" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.12"/>
                        <line x1="970" y1="440" x2="1190" y2="300" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="970" y1="440" x2="1190" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>
                        <line x1="970" y1="560" x2="1190" y2="440" stroke="#7c3aed" stroke-width="0.6" opacity="0.1"/>
                        <line x1="970" y1="560" x2="1190" y2="580" stroke="#7c3aed" stroke-width="0.6" opacity="0.15"/>

                        <!-- Layer 1 节点 -->
                        <circle cx="160" cy="160" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="160" cy="160" r="2" fill="#a78bfa" opacity="0.6"/>
                        <circle cx="160" cy="300" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="160" cy="300" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="160" cy="440" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="160" cy="440" r="2" fill="#a78bfa" opacity="0.6"/>
                        <circle cx="160" cy="580" r="4.5" fill="#8b5cf6" opacity="0.35"/>
                        <circle cx="160" cy="580" r="1.8" fill="#a78bfa" opacity="0.55"/>

                        <!-- Layer 2 节点 -->
                        <circle cx="380" cy="200" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="380" cy="200" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="380" cy="320" r="7" fill="#8b5cf6" opacity="0.5"/>
                        <circle cx="380" cy="320" r="3" fill="#a78bfa" opacity="0.7"/>
                        <circle cx="380" cy="440" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="380" cy="440" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="380" cy="560" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="380" cy="560" r="2" fill="#a78bfa" opacity="0.6"/>

                        <!-- Layer 4 节点 -->
                        <circle cx="970" cy="200" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="970" cy="200" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="970" cy="320" r="7" fill="#8b5cf6" opacity="0.5"/>
                        <circle cx="970" cy="320" r="3" fill="#a78bfa" opacity="0.7"/>
                        <circle cx="970" cy="440" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="970" cy="440" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="970" cy="560" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="970" cy="560" r="2" fill="#a78bfa" opacity="0.6"/>

                        <!-- Layer 5 节点 -->
                        <circle cx="1190" cy="160" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="1190" cy="160" r="2" fill="#a78bfa" opacity="0.6"/>
                        <circle cx="1190" cy="300" r="6" fill="#8b5cf6" opacity="0.45"/>
                        <circle cx="1190" cy="300" r="2.5" fill="#a78bfa" opacity="0.65"/>
                        <circle cx="1190" cy="440" r="5" fill="#8b5cf6" opacity="0.4"/>
                        <circle cx="1190" cy="440" r="2" fill="#a78bfa" opacity="0.6"/>
                        <circle cx="1190" cy="580" r="4.5" fill="#8b5cf6" opacity="0.35"/>
                        <circle cx="1190" cy="580" r="1.8" fill="#a78bfa" opacity="0.55"/>

                        <!-- 脉冲信号线 - 从L2到中心 -->
                        <path d="M380 200 Q480 240 520 300" stroke="#a78bfa" stroke-width="1" fill="none" opacity="0.2" stroke-dasharray="4 6"/>
                        <path d="M380 320 Q460 340 520 340" stroke="#a78bfa" stroke-width="1.2" fill="none" opacity="0.25" stroke-dasharray="4 6"/>
                        <path d="M380 440 Q480 420 520 380" stroke="#a78bfa" stroke-width="1" fill="none" opacity="0.2" stroke-dasharray="4 6"/>

                        <!-- 脉冲信号线 - 从中心到L4 -->
                        <path d="M830 300 Q870 240 970 200" stroke="#a78bfa" stroke-width="1" fill="none" opacity="0.2" stroke-dasharray="4 6"/>
                        <path d="M830 340 Q890 340 970 320" stroke="#a78bfa" stroke-width="1.2" fill="none" opacity="0.25" stroke-dasharray="4 6"/>
                        <path d="M830 380 Q870 420 970 440" stroke="#a78bfa" stroke-width="1" fill="none" opacity="0.2" stroke-dasharray="4 6"/>
                    </svg>

                    <!-- 中央核心区域 -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center relative">
                            <!-- 六边形框 -->
                            <div class="relative inline-block mb-8">
                                <svg class="w-[140px] h-[140px]" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
                                    <polygon points="70,4 130,37 130,103 70,136 10,103 10,37" fill="none" stroke="#8b5cf6" stroke-width="1.5" opacity="0.5"/>
                                    <polygon points="70,18 118,45 118,95 70,122 22,95 22,45" fill="none" stroke="#7c3aed" stroke-width="0.8" opacity="0.3"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[48px] font-mono font-bold text-violet-400 leading-none">01</span>
                                </div>
                            </div>

                            <!-- 标签 -->
                            <div class="flex items-center justify-center gap-3 mb-4">
                                <div class="w-[30px] h-[2px] bg-violet-600"></div>
                                <span class="text-[11px] font-mono text-violet-500 tracking-[0.3em] uppercase">Neural Section</span>
                                <div class="w-[30px] h-[2px] bg-violet-600"></div>
                            </div>

                            <!-- 标题 -->
                            <h1 class="text-[38px] font-bold text-white mb-3 tracking-wider">{{title}}</h1>

                            <!-- 副标题 -->
                            <p class="text-base text-violet-400 tracking-[0.15em]">{{subtitle}}</p>

                            <!-- 底部节点装饰 -->
                            <div class="flex items-center justify-center gap-3 mt-8">
                                <div class="w-1.5 h-1.5 rounded-full bg-violet-600"></div>
                                <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-violet-400"></div>
                                <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-violet-600"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 左侧层标签 -->
                    <div class="absolute top-[120px] left-[140px] text-[9px] font-mono text-violet-700 tracking-widest">INPUT</div>
                    <div class="absolute top-[160px] left-[358px] text-[9px] font-mono text-violet-700 tracking-widest">HIDDEN_1</div>

                    <!-- 右侧层标签 -->
                    <div class="absolute top-[160px] right-[140px] text-[9px] font-mono text-violet-700 tracking-widest">OUTPUT</div>
                    <div class="absolute top-[160px] right-[340px] text-[9px] font-mono text-violet-700 tracking-widest">HIDDEN_2</div>

                    <!-- 顶部状态 -->
                    <div class="absolute top-4 left-6 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-violet-500 opacity-60"></div>
                        <span class="text-[10px] font-mono text-violet-700 tracking-widest">NEURAL_NET</span>
                    </div>
                    <div class="absolute top-4 right-6 text-[10px] font-mono text-violet-700 tracking-widest">EPOCH: 1,024</div>

                    <!-- 底部状态 -->
                    <div class="absolute bottom-4 left-6 text-[10px] font-mono text-violet-700 tracking-wider">ACCURACY: 99.7%</div>
                    <div class="absolute bottom-4 right-6 text-[10px] font-mono text-violet-700 tracking-wider">LAYERS: 5 | NODES: 20</div>

                </div>
            </div>
