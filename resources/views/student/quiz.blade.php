<x-dashboard-layout>
    <x-slot name="title">Smart Drill Quiz</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    {{-- KaTeX Math Rendering Assets --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>

    <div x-data="quizData()" class="max-w-4xl mx-auto space-y-6">

        {{-- ────────────────────────────────────────────────────────── --}}
        {{-- VIEW 1: ACTIVE QUIZ VIEW --}}
        {{-- ────────────────────────────────────────────────────────── --}}
        <div x-show="!isReviewing" class="space-y-6">

            {{-- TOP HEADER & NAV BAR --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <a href="{{ route('student.learning') }}" class="text-xs font-bold text-gray-400 hover:text-blue-600 inline-flex items-center gap-1 mb-1 transition-colors">
                        ← Back to My Learning
                    </a>
                    <h2 class="font-heading font-black text-xl text-gray-900">{{ isset($quiz) ? $quiz->title : 'Smart Practice Drill' }}</h2>
                    <p class="text-xs text-gray-500 font-medium">{{ isset($quiz) ? ($quiz->course->subject->name ?? 'General') . ' • ' . ($quiz->course->name ?? '') : 'General Subject Practice' }} • <span x-text="questions.length"></span> Questions</p>
                </div>

                {{-- Timer Badge --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="px-4 py-2 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 font-mono font-black text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="formatTimer()"></span>
                    </div>
                </div>
            </div>

            {{-- PROGRESS BAR --}}
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-xs font-extrabold text-gray-700">
                    <span>Question <span x-text="currentQuestion + 1"></span> of <span x-text="questions.length"></span></span>
                    <span class="text-blue-600" x-text="Math.round(((currentQuestion + 1) / questions.length) * 100) + '% Completed'"></span>
                </div>
                <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 transition-all duration-300 rounded-full" :style="'width: ' + (((currentQuestion + 1) / questions.length) * 100) + '%'"></div>
                </div>
            </div>

            {{-- MAIN QUESTION CARD --}}
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6">
                
                {{-- Question Title --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider">
                            Question <span x-text="currentQuestion + 1"></span>
                        </span>
                        <span class="text-xs font-bold text-red-500">* Required</span>
                    </div>
                    <h3 x-show="questions[currentQuestion].question" class="font-heading font-black text-lg sm:text-xl text-gray-900 leading-snug" x-html="formatMath(questions[currentQuestion].question)"></h3>
                    <template x-if="questions[currentQuestion].image_path">
                        <div class="mt-3">
                            <img :src="'/' + questions[currentQuestion].image_path" class="max-h-80 rounded-2xl mx-auto border border-gray-200 shadow-sm object-contain" alt="Question Image">
                        </div>
                    </template>
                </div>

                {{-- Options List --}}
                <div class="space-y-3">
                    <template x-for="option in questions[currentQuestion].options" :key="option.id">
                        <button type="button" 
                                @click="selectOption(option.id)"
                                :class="{
                                    'border-blue-600 bg-blue-50/60 ring-2 ring-blue-500/20 text-blue-900 font-extrabold': selectedAnswers[currentQuestion] === option.id,
                                    'border-gray-200 bg-white hover:border-gray-300 text-gray-800 font-medium': selectedAnswers[currentQuestion] !== option.id
                                }"
                                class="w-full p-4 rounded-2xl border text-left flex items-center gap-4 transition-all cursor-pointer">
                            
                            {{-- Choice Badge (A, B, C, D) --}}
                            <div :class="{
                                'bg-blue-600 text-white': selectedAnswers[currentQuestion] === option.id,
                                'bg-gray-100 text-gray-600': selectedAnswers[currentQuestion] !== option.id
                            }" class="w-8 h-8 rounded-xl font-heading font-black text-xs flex items-center justify-center shrink-0 transition-colors">
                                <span x-text="option.id"></span>
                            </div>

                            <span class="text-sm" x-html="formatMath(option.text)"></span>
                        </button>
                    </template>
                </div>

                {{-- Required Answer Alert --}}
                <div x-show="requireAnswerAlert" x-cloak class="p-3.5 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 font-extrabold flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please select an answer to proceed. Answering is required.</span>
                </div>

                {{-- Navigation Buttons --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button type="button" 
                            @click="prevQuestion()" 
                            :disabled="currentQuestion === 0"
                            :class="currentQuestion === 0 ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-gray-100 hover:bg-gray-200 text-gray-800'"
                            class="py-2.5 px-5 rounded-2xl font-extrabold text-xs transition-all flex items-center gap-1.5">
                        ← Previous
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button" 
                                x-show="currentQuestion < questions.length - 1"
                                @click="nextQuestion()" 
                                :class="!selectedAnswers[currentQuestion] ? 'opacity-60 cursor-not-allowed bg-blue-400 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'"
                                class="py-2.5 px-6 rounded-2xl font-extrabold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            Next Question →
                        </button>

                        <button type="button" 
                                x-show="currentQuestion === questions.length - 1"
                                @click="submitQuiz()" 
                                :class="!selectedAnswers[currentQuestion] ? 'opacity-60 cursor-not-allowed bg-emerald-400 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md'"
                                class="py-2.5 px-6 rounded-2xl font-extrabold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            Submit Drill Result ✓
                        </button>
                    </div>
                </div>

            </div>

        </div>

        {{-- ────────────────────────────────────────────────────────── --}}
        {{-- VIEW 2: REVIEW MODE VIEW (WITH TIPS FOR EACH QUESTION) --}}
        {{-- ────────────────────────────────────────────────────────── --}}
        <div x-show="isReviewing" x-cloak class="space-y-6">

            {{-- REVIEW TOP BANNER --}}
            <div class="bg-gradient-to-r from-primary-950 via-primary-900 to-indigo-900 text-white p-6 sm:p-7 rounded-3xl shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-black uppercase tracking-wider">
                        Result Review Mode
                    </span>
                    <h2 class="font-heading font-black text-2xl text-white">Mathematics Smart Drill Review</h2>
                    <p class="text-xs text-gray-300 font-medium">Review your submitted choices and detailed teacher tips for each question.</p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/20 text-center">
                        <span class="text-[10px] font-bold text-gray-300 uppercase block">Score</span>
                        <span class="text-xl font-black text-white" x-text="score + '%'"></span>
                    </div>
                    <button type="button" @click="showModal = true; isReviewing = false" class="py-2.5 px-4 rounded-2xl bg-white text-blue-900 font-extrabold text-xs shadow-md hover:bg-blue-50 transition-all cursor-pointer">
                        Summary View
                    </button>
                </div>
            </div>

            {{-- REVIEW QUESTIONS LIST --}}
            <div class="space-y-6">
                <template x-for="(q, idx) in questions" :key="q.id">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-5">
                        
                        {{-- Question Header & Correct/Incorrect Status Badge --}}
                        <div class="flex items-center justify-between gap-4">
                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider">
                                Question <span x-text="idx + 1"></span>
                            </span>

                            <span :class="selectedAnswers[idx] === q.correctAnswer ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'"
                                  class="px-3 py-1 rounded-full border text-xs font-black flex items-center gap-1.5">
                                <span x-text="selectedAnswers[idx] === q.correctAnswer ? 'Correct ✓' : 'Incorrect ✗'"></span>
                            </span>
                        </div>

                        {{-- Question Text --}}
                        <h3 x-show="q.question" class="font-heading font-black text-base sm:text-lg text-gray-900" x-html="formatMath(q.question)"></h3>
                        <template x-if="q.image_path">
                            <div class="mt-3">
                                <img :src="'/' + q.image_path" class="max-h-80 rounded-2xl mx-auto border border-gray-200 shadow-sm object-contain" alt="Question Image">
                            </div>
                        </template>

                        {{-- Options List with Review Colors --}}
                        <div class="space-y-2.5">
                            <template x-for="opt in q.options" :key="opt.id">
                                <div :class="{
                                         'border-emerald-500 bg-emerald-50/70 text-emerald-950 font-extrabold ring-2 ring-emerald-500/20': opt.id === q.correctAnswer,
                                         'border-red-400 bg-red-50/70 text-red-950 font-extrabold ring-2 ring-red-400/20': opt.id === selectedAnswers[idx] && opt.id !== q.correctAnswer,
                                         'border-gray-200 bg-gray-50/50 text-gray-600': opt.id !== q.correctAnswer && opt.id !== selectedAnswers[idx]
                                     }"
                                     class="p-3.5 rounded-2xl border flex items-center justify-between gap-3 text-xs transition-all">
                                    
                                    <div class="flex items-center gap-3">
                                        <div :class="{
                                                 'bg-emerald-600 text-white': opt.id === q.correctAnswer,
                                                 'bg-red-600 text-white': opt.id === selectedAnswers[idx] && opt.id !== q.correctAnswer,
                                                 'bg-gray-200 text-gray-600': opt.id !== q.correctAnswer && opt.id !== selectedAnswers[idx]
                                             }"
                                             class="w-7 h-7 rounded-xl font-heading font-black text-xs flex items-center justify-center shrink-0">
                                            <span x-text="opt.id"></span>
                                        </div>
                                        <span x-html="formatMath(opt.text)"></span>
                                    </div>

                                    {{-- Right Indicator Label --}}
                                    <div>
                                        <span x-show="opt.id === q.correctAnswer" class="px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase">Correct Answer</span>
                                        <span x-show="opt.id === selectedAnswers[idx] && opt.id !== q.correctAnswer" class="px-2.5 py-0.5 rounded-full bg-red-600 text-white text-[10px] font-black uppercase">Your Choice</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- TEACHER TIP & EXPLANATION BOX --}}
                        <div class="p-4 bg-purple-50/80 border border-purple-100 rounded-2xl text-xs space-y-1.5">
                            <span class="font-extrabold text-purple-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Solution Tip & Explanation:
                            </span>
                            <p class="text-purple-950 font-medium leading-relaxed" x-html="formatMath(q.tip)"></p>
                        </div>

                    </div>
                </template>
            </div>

            {{-- REVIEW FOOTER ACTIONS --}}
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" @click="restartQuiz()" class="w-full sm:w-auto py-3 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">
                    Try Again (Restart Drill)
                </button>
                <a href="{{ route('student.learning') }}" class="w-full sm:w-auto py-3 px-6 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs text-center transition-all">
                    Return to My Learning
                </a>
            </div>

        </div>

        {{-- ── SCORE SUMMARY RESULTS MODAL ── --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6 text-center">
                
                {{-- Trophy / Badge Icon --}}
                <div :class="score >= 80 ? 'bg-amber-100 text-amber-600' : (score >= 60 ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-600')" class="w-16 h-16 rounded-3xl mx-auto flex items-center justify-center font-heading font-black text-2xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>

                <div class="space-y-1">
                    <span :class="score >= 80 ? 'bg-amber-50 text-amber-700' : (score >= 60 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-700')" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">Drill Completed</span>
                    <h3 class="font-heading font-black text-2xl text-gray-900" x-text="score === 100 ? 'Perfect Score!' : (score >= 60 ? 'Great Job!' : 'Keep Practicing!')"></h3>
                    <p class="text-xs text-gray-500 font-medium">Mathematics Chapter 3 Smart Drill Summary</p>
                </div>

                {{-- Score & Dynamic XP Card --}}
                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 space-y-3">
                    
                    {{-- Score Percentage --}}
                    <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                        <span>Overall Score</span>
                        <div class="text-right">
                            <span class="text-2xl font-black text-blue-600" x-text="score + '%'"></span>
                            <span class="text-[10px] text-gray-400 block font-medium" x-text="`(${correctCount} / ${questions.length} Correct)`"></span>
                        </div>
                    </div>

                    {{-- Dynamic Experience Points (XP) --}}
                    <div class="flex items-center justify-between text-xs font-bold pt-3 border-t border-gray-200">
                        <span>Experience Points (XP)</span>
                        <span :class="earnedXP >= 80 ? 'text-amber-600 bg-amber-50 border-amber-200' : (earnedXP >= 40 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-blue-600 bg-blue-50 border-blue-200')" 
                              class="px-3 py-1 rounded-full border text-xs font-black" 
                              x-text="`+${earnedXP} XP`"></span>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2 pt-2">
                    <button type="button" @click="startReview()" class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Review Answers & Teacher Tips</span>
                    </button>
                    <button type="button" @click="restartQuiz()" class="w-full py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs transition-all cursor-pointer">
                        Try Again (Restart Drill)
                    </button>
                    <a href="{{ route('student.learning') }}" class="block w-full py-2 text-xs font-extrabold text-gray-400 hover:text-gray-600 transition-all">
                        Return to My Learning
                    </a>
                </div>

            </div>
        </div>

    </div>

    <script>
        function quizData() {
            return {
                quizId: {{ isset($quiz) ? $quiz->id : 0 }},
                currentQuestion: 0,
                selectedAnswers: {},
                requireAnswerAlert: false,
                isCompleted: false,
                showModal: false,
                isReviewing: false,
                score: 0,
                correctCount: 0,
                earnedXP: 0,
                timerSeconds: {{ isset($quiz) && $quiz->questions->count() > 0 ? ($quiz->questions->count() * 60) : 300 }},
                timerInterval: null,
                
                questions: [
                    @if(isset($quiz) && $quiz->questions->count() > 0)
                        @foreach($quiz->questions as $idx => $q)
                        {
                            id: {{ $q->id }},
                            type: "{{ $q->type }}",
                            image_path: "{{ $q->image_path }}",
                            subject: "{{ $quiz->course->subject->name ?? 'General' }}",
                            topic: "{{ $quiz->title }}",
                            question: @json($q->question_text ?? ''),
                            options: [
                                { id: 'A', text: @json($q->option_a) },
                                { id: 'B', text: @json($q->option_b) },
                                { id: 'C', text: @json($q->option_c) },
                                { id: 'D', text: @json($q->option_d) }
                            ],
                            correctAnswer: "{{ $q->correct_option }}",
                            tip: @json($q->explanation ?? 'No tip available.')
                        }{{ $loop->last ? '' : ',' }}
                        @endforeach
                    @else
                        {
                            id: 1,
                            type: 'text',
                            image_path: '',
                            subject: 'Mathematics',
                            topic: 'Algebra Practice',
                            question: 'Solve for x: 3x + 9 = 24',
                            options: [
                                { id: 'A', text: 'x = 3' },
                                { id: 'B', text: 'x = 5' },
                                { id: 'C', text: 'x = 7' },
                                { id: 'D', text: 'x = 9' }
                            ],
                            correctAnswer: 'B',
                            tip: 'Tip: Subtract 9 from both sides first (3x = 15), then divide by 3 to find x = 5.'
                        }
                    @endif
                ],

                init() {
                    this.timerInterval = setInterval(() => {
                        if (this.timerSeconds > 0 && !this.isCompleted) {
                            this.timerSeconds--;
                        } else if (this.timerSeconds === 0) {
                            this.submitQuiz();
                        }
                    }, 1000);
                },

                formatTimer() {
                    const m = Math.floor(this.timerSeconds / 60).toString().padStart(2, '0');
                    const s = (this.timerSeconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                selectOption(optionId) {
                    if (this.isReviewing) return;
                    this.selectedAnswers[this.currentQuestion] = optionId;
                    this.requireAnswerAlert = false;
                },

                nextQuestion() {
                    if (!this.selectedAnswers[this.currentQuestion]) {
                        this.requireAnswerAlert = true;
                        return;
                    }
                    this.requireAnswerAlert = false;
                    if (this.currentQuestion < this.questions.length - 1) {
                        this.currentQuestion++;
                    }
                },

                prevQuestion() {
                    this.requireAnswerAlert = false;
                    if (this.currentQuestion > 0) {
                        this.currentQuestion--;
                    }
                },

                submitQuiz() {
                    for (let i = 0; i < this.questions.length; i++) {
                        if (!this.selectedAnswers[i]) {
                            this.currentQuestion = i;
                            this.requireAnswerAlert = true;
                            return;
                        }
                    }

                    let correct = 0;
                    this.questions.forEach((q, idx) => {
                        if (this.selectedAnswers[idx] === q.correctAnswer) {
                            correct++;
                        }
                    });
                    this.correctCount = correct;
                    this.score = Math.round((correct / this.questions.length) * 100);
                    this.earnedXP = correct * 20;

                    this.isCompleted = true;
                    this.showModal = true;
                    clearInterval(this.timerInterval);

                    // Save results to database if it's a dynamic quiz
                    if (this.quizId > 0) {
                        fetch(`/student/quiz/${this.quizId}/submit`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                score: this.correctCount,
                                total_questions: this.questions.length
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log('Quiz score saved:', data);
                        })
                        .catch(err => console.error('Error saving quiz score:', err));
                    }
                },

                formatMath(text) {
                    if (!text) return '';
                    
                    // If text contains KaTeX inline or block delimiters, render them using KaTeX
                    if (window.katex && (text.includes('\\(') || text.includes('$$'))) {
                        try {
                            let parsed = text;
                            parsed = parsed.replace(/\\\( (.*?) \\\)/g, (match, eq) => {
                                return katex.renderToString(eq, { throwOnError: false, displayMode: false });
                            });
                            parsed = parsed.replace(/\\\((.*?)\\\)/g, (match, eq) => {
                                return katex.renderToString(eq, { throwOnError: false, displayMode: false });
                            });
                            parsed = parsed.replace(/\$\$(.*?)\$\$/g, (match, eq) => {
                                return katex.renderToString(eq, { throwOnError: false, displayMode: true });
                            });
                            return parsed;
                        } catch (e) {
                            console.error('KaTeX parser error:', e);
                        }
                    }

                    // Fallback to regex replacements for superscripts
                    let formatted = text.replace(/\^([0-9a-zA-Z\+\-]+)/g, '<sup>$1</sup>')
                                        .replace(/\^\((.*?)\)/g, '<sup>$1</sup>');
                    // Convert letter + space + digit (e.g. w 2) to superscript (e.g. w²)
                    formatted = formatted.replace(/([a-zA-Z])\s+(\d)(?!\w)/g, '$1<sup>$2</sup>');
                    return formatted;
                },

                startReview() {
                    this.showModal = false;
                    this.isReviewing = true;
                },

                restartQuiz() {
                    this.currentQuestion = 0;
                    this.selectedAnswers = {};
                    this.requireAnswerAlert = false;
                    this.isCompleted = false;
                    this.showModal = false;
                    this.isReviewing = false;
                    this.score = 0;
                    this.correctCount = 0;
                    this.earnedXP = 0;
                    this.timerSeconds = this.questions.length * 60;
                    this.init();
                }
            };
        }
    </script>
</x-dashboard-layout>
