<?php
include("conn.php");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>المساعد الذكي - منقذون</title>
  
  <!-- Bootstrap & FontAwesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #4CAF50;
      --secondary-color: #8BC34A;
      --accent-color: #FFC107;
      --dark-color: #2E7D32;
      --light-color: #F1F8E9;
      --text-color: #333;
      --white-color: #fff;
    }
    
    * {
      font-family: 'Tajawal', sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background-color: var(--light-color);
      color: var(--text-color);
      overflow-x: hidden;
      min-height: 100vh;
    }
    
    .ai-assistant-page {
      padding-top: 100px;
      padding-bottom: 50px;
      min-height: 100vh;
      background: linear-gradient(135deg, rgba(241, 248, 233, 0.8), rgba(220, 237, 200, 0.8));
    }
    
    .ai-header {
      text-align: center;
      margin-bottom: 40px;
    }
    
    .ai-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 15px;
    }
    
    .ai-header p {
      font-size: 1.2rem;
      color: var(--text-color);
      max-width: 800px;
      margin: 0 auto;
    }
    
    .ai-container {
      max-width: 1000px;
      margin: 0 auto;
      background: var(--white-color);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      position: relative;
    }
    
    .ai-sidebar {
      background: linear-gradient(to bottom, var(--primary-color), var(--dark-color));
      color: white;
      padding: 30px;
      border-radius: 20px 0 0 20px;
      height: 100%;
    }
    
    .ai-sidebar h3 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      font-weight: 700;
      position: relative;
      padding-bottom: 15px;
    }
    
    .ai-sidebar h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      right: 0;
      width: 50px;
      height: 3px;
      background: var(--accent-color);
      border-radius: 3px;
    }
    
    .suggestion-item {
      background: rgba(255, 255, 255, 0.1);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .suggestion-item:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-3px);
    }
    
    .suggestion-item i {
      margin-left: 10px;
      color: var(--accent-color);
    }
    
    .chat-container {
      height: 600px;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }
    
    .chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 15px;
      margin-bottom: 20px;
    }
    
    .message {
      margin-bottom: 15px;
      display: flex;
    }
    
    .user-message {
      justify-content: flex-end;
    }
    
    .bot-message {
      justify-content: flex-start;
    }
    
    .message-content {
      padding: 12px 18px;
      border-radius: 18px;
      max-width: 80%;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    
    .user-message .message-content {
      background-color: var(--primary-color);
      color: white;
      border-bottom-right-radius: 5px;
    }
    
    .bot-message .message-content {
      background-color: white;
      color: var(--text-color);
      border-bottom-left-radius: 5px;
      border: 1px solid #e9ecef;
    }
    
    .message-time {
      font-size: 0.75rem;
      color: rgba(0, 0, 0, 0.5);
      margin-top: 5px;
      text-align: left;
    }
    
    .bot-message .message-time {
      text-align: right;
    }
    
    .chat-input-container {
      display: flex;
      gap: 10px;
      position: relative;
    }
    
    .chat-input {
      flex: 1;
      border-radius: 30px;
      padding: 15px 20px;
      border: 1px solid #ddd;
      font-size: 1rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }
    
    .chat-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 2px 15px rgba(76, 175, 80, 0.2);
      outline: none;
    }
    
    .send-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
      color: white;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }
    
    .send-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    .send-btn i {
      font-size: 1.2rem;
    }
    
    .typing-indicator {
      display: flex;
      align-items: center;
      padding: 10px 15px;
    }
    
    .typing-indicator span {
      height: 8px;
      width: 8px;
      margin: 0 1px;
      background-color: #bbb;
      border-radius: 50%;
      display: inline-block;
      opacity: 0.4;
    }
    
    .typing-indicator span:nth-child(1) {
      animation: pulse 1s infinite;
    }
    
    .typing-indicator span:nth-child(2) {
      animation: pulse 1s infinite 0.2s;
    }
    
    .typing-indicator span:nth-child(3) {
      animation: pulse 1s infinite 0.4s;
    }
    
    @keyframes pulse {
      0% {
        opacity: 0.4;
        transform: scale(1);
      }
      50% {
        opacity: 1;
        transform: scale(1.2);
      }
      100% {
        opacity: 0.4;
        transform: scale(1);
      }
    }
    
    .ai-features {
      margin-top: 60px;
      text-align: center;
    }
    
    .ai-features h2 {
      font-size: 2rem;
      color: var(--dark-color);
      margin-bottom: 30px;
    }
    
    .feature-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      height: 100%;
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 1.8rem;
    }
    
    .feature-card h3 {
      font-size: 1.5rem;
      color: var(--dark-color);
      margin-bottom: 15px;
    }
    
    .feature-card p {
      color: var(--text-color);
      font-size: 1rem;
    }
    
    @media (max-width: 992px) {
      .ai-sidebar {
        border-radius: 20px 20px 0 0;
        margin-bottom: 20px;
      }
    }
    
    @media (max-width: 768px) {
      .ai-header h1 {
        font-size: 2rem;
      }
      
      .ai-header p {
        font-size: 1rem;
      }
      
      .chat-container {
        height: 500px;
      }
    }
  </style>
</head>
<body>
  <!-- Include Header -->
  <?php include('header.php'); ?>
  
  <div class="ai-assistant-page">
    <div class="container">
      <div class="ai-header">
        <h1><i class="fas fa-robot me-2"></i> المساعد الذكي</h1>
        <p>مرحباً بك في المساعد الذكي لمنصة منقذون! يمكنك طرح أي سؤال حول رعاية الحيوانات، الإسعافات الأولية، أو الخدمات التي نقدمها.</p>
      </div>
      
      <div class="ai-container">
        <div class="row g-0">
          <div class="col-lg-3">
            <div class="ai-sidebar">
              <h3>اقتراحات</h3>
              <div class="suggestions-list">
                <div class="suggestion-item" onclick="setQuestion('كيف أقدم الإسعافات الأولية لحيوان مصاب؟')">
                  <i class="fas fa-first-aid"></i>
                  الإسعافات الأولية للحيوانات
                </div>
                <div class="suggestion-item" onclick="setQuestion('ما هي علامات المرض عند القطط؟')">
                  <i class="fas fa-cat"></i>
                  علامات المرض عند القطط
                </div>
                <div class="suggestion-item" onclick="setQuestion('كيف أعتني بجرو صغير؟')">
                  <i class="fas fa-dog"></i>
                  العناية بالجراء الصغيرة
                </div>
                <div class="suggestion-item" onclick="setQuestion('ما هي الأطعمة الممنوعة للحيوانات الأليفة؟')">
                  <i class="fas fa-utensils"></i>
                  الأطعمة الممنوعة للحيوانات
                </div>
                <div class="suggestion-item" onclick="setQuestion('كيف أبلغ عن حالة حيوان مصاب في الشارع؟')">
                  <i class="fas fa-ambulance"></i>
                  الإبلاغ عن حيوان مصاب
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-9">
            <div class="chat-container">
              <div class="chat-messages" id="chatMessages">
                <div class="message bot-message">
                  <div class="message-content">
                    مرحباً بك في المساعد الذكي لمنصة منقذون! كيف يمكنني مساعدتك اليوم؟
                    <div class="message-time">الآن</div>
                  </div>
                </div>
              </div>
              <div class="chat-input-container">
                <input type="text" id="userMessage" class="chat-input" placeholder="اكتب سؤالك هنا..." />
                <button id="sendMessage" class="send-btn">
                  <i class="fas fa-paper-plane"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="ai-features">
        <h2>كيف يمكن للمساعد الذكي مساعدتك؟</h2>
        <div class="row mt-4">
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-heartbeat"></i>
              </div>
              <h3>إسعافات أولية</h3>
              <p>احصل على معلومات سريعة حول كيفية التعامل مع الحالات الطارئة للحيوانات والإسعافات الأولية الأساسية.</p>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-book-medical"></i>
              </div>
              <h3>نصائح صحية</h3>
              <p>احصل على نصائح وإرشادات للعناية بصحة حيوانك الأليف والوقاية من الأمراض الشائعة.</p>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-clinic-medical"></i>
              </div>
              <h3>معلومات عن الخدمات</h3>
              <p>استفسر عن الخدمات المتاحة في منصة منقذون، مواعيد العيادات، والأطباء المتخصصين.</p>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-paw"></i>
              </div>
              <h3>سلوكيات الحيوانات</h3>
              <p>فهم سلوكيات الحيوانات الأليفة وكيفية التعامل معها بشكل صحيح وتدريبها.</p>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-utensils"></i>
              </div>
              <h3>التغذية السليمة</h3>
              <p>معلومات عن النظام الغذائي المناسب لمختلف أنواع الحيوانات والأطعمة الممنوعة.</p>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="fas fa-hand-holding-heart"></i>
              </div>
              <h3>الإنقاذ والتبني</h3>
              <p>معلومات حول كيفية إنقاذ الحيوانات المشردة وعملية التبني وما يجب مراعاته.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Chat functionality
    document.addEventListener('DOMContentLoaded', function() {
      const chatMessages = document.getElementById('chatMessages');
      const userMessage = document.getElementById('userMessage');
      const sendMessage = document.getElementById('sendMessage');
      
      // Function to add a message to the chat
      function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        
        const now = new Date();
        const timeString = now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
        
        messageDiv.innerHTML = `
          <div class="message-content">
            ${message}
            <div class="message-time">${timeString}</div>
          </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }
      
      // Function to show typing indicator
      function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.id = 'typingIndicator';
        
        typingDiv.innerHTML = `
          <div class="message-content typing-indicator">
            <span></span>
            <span></span>
            <span></span>
          </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }
      
      // Function to remove typing indicator
      function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
          typingIndicator.remove();
        }
      }
      
      // Function to get AI response
      function getAIResponse(userQuery) {
        // Normalize the query by removing extra spaces and converting to lowercase
        const normalizedQuery = userQuery.trim().toLowerCase();
        
        // Comprehensive predefined responses for common questions
        const responses = {
          // First aid and emergency care
          'كيف أقدم الإسعافات الأولية لحيوان مصاب؟':
            `للإسعافات الأولية للحيوانات المصابة:
            <br>1. حافظ على هدوئك وتعامل بحذر لتجنب العض.
            <br>2. قيّم الحالة: تنفس، نزيف، كسور.
            <br>3. للنزيف: اضغط بقطعة قماش نظيفة.
            <br>4. للكسور: ثبت المنطقة دون تحريكها.
            <br>5. للحروق: ضع ماء بارد (ليس مثلجاً).
            <br>6. انقل الحيوان للطبيب البيطري بأسرع وقت.`,
        
          'ماذا أفعل إذا تعرض حيواني للتسمم؟':
            `في حالة تسمم الحيوان:
            <br>1. حدد مصدر التسمم إن أمكن.
            <br>2. لا تحاول إجبار الحيوان على التقيؤ إلا بتوجيه من الطبيب.
            <br>3. احتفظ بعينة من المادة السامة إن وجدت.
            <br>4. اتصل فوراً بالطبيب البيطري أو خط الطوارئ.
            <br>5. انقل الحيوان للعيادة البيطرية بأسرع وقت ممكن.`,
        
          'كيف أتعامل مع جرح مفتوح عند الحيوان؟':
            `للتعامل مع الجروح المفتوحة:
            <br>1. ارتدِ قفازات إن أمكن.
            <br>2. نظف الجرح برفق بماء دافئ ومحلول ملحي.
            <br>3. أزل الأوساخ والشعر من حول الجرح.
            <br>4. ضع مطهراً خفيفاً مثل البيتادين المخفف.
            <br>5. غطِ الجرح بضمادة نظيفة.
            <br>6. راجع الطبيب البيطري خاصة للجروح العميقة أو الكبيرة.`,
        
          'ماذا أفعل إذا كان حيواني يعاني من صعوبة في التنفس؟':
            `صعوبة التنفس حالة طارئة:
            <br>1. حافظ على هدوء الحيوان وتجنب إجهاده.
            <br>2. تأكد من عدم وجود أي شيء يعيق مجرى التنفس.
            <br>3. ضع الحيوان في وضعية مريحة مع رفع رأسه قليلاً.
            <br>4. انقله فوراً إلى أقرب عيادة بيطرية.
            <br>5. لا تغطي أنف أو فم الحيوان.
            <br>هذه حالة طارئة تستدعي العناية الطبية الفورية.`,
        
          // Cat health and care
          'ما هي علامات المرض عند القطط؟':
            `علامات المرض الشائعة عند القطط تشمل:
            <br>1. تغير في الشهية (زيادة أو نقصان).
            <br>2. خمول وقلة نشاط غير معتادة.
            <br>3. تغير في عادات استخدام صندوق الفضلات.
            <br>4. قيء أو إسهال.
            <br>5. تغير في كمية شرب الماء.
            <br>6. صعوبة في التنفس.
            <br>7. تساقط الشعر بشكل غير طبيعي.
            <br>8. تغير في سلوك القطة.
            <br>إذا لاحظت أياً من هذه العلامات، يجب استشارة الطبيب البيطري.`,
        
          'كيف أعتني بقطة حديثة الولادة؟':
            `للعناية بالقطط حديثة الولادة:
            <br>1. اتركها مع الأم إن أمكن، فهي المصدر الأفضل للرعاية والغذاء.
            <br>2. وفر مكاناً دافئاً وهادئاً للأم وصغارها.
            <br>3. إذا كانت يتيمة، استخدم حليباً خاصاً للقطط (ليس حليب البقر).
            <br>4. أطعمها كل 2-3 ساعات باستخدام زجاجة خاصة.
            <br>5. ساعدها على التبول والتبرز بعد الرضاعة بتدليك المنطقة بقطنة دافئة.
            <br>6. راقب وزنها للتأكد من نموها بشكل صحيح.
            <br>7. استشر الطبيب البيطري للحصول على جدول التطعيمات المناسب.`,
        
          'متى يجب تطعيم القطط؟':
            `جدول تطعيمات القطط:
            <br>1. 6-8 أسابيع: التطعيم الثلاثي الأول (القطط)
            <br>2. 10-12 أسبوع: التطعيم الثلاثي الثاني + تطعيم داء الكلب الأول
            <br>3. 14-16 أسبوع: التطعيم الثلاثي الثالث + تطعيم داء الكلب الثاني
            <br>4. سنوياً: جرعات تنشيطية
            <br>يجب استشارة الطبيب البيطري لتحديد الجدول المناسب لقطتك حسب بيئتها ومخاطر الأمراض المحلية.`,
        
          'كيف أتعامل مع سلوك الخدش عند القطط؟':
            `للتعامل مع سلوك الخدش:
            <br>1. وفر عدة أماكن مخصصة للخدش (أعمدة خدش).
            <br>2. ضع أعمدة الخدش بالقرب من أماكن نوم القطة أو راحتها.
            <br>3. شجع القطة على استخدام أعمدة الخدش بوضع القليل من الكاتنيب عليها.
            <br>4. قلّم أظافر القطة بانتظام باستخدام مقص أظافر خاص.
            <br>5. لا تعاقب القطة على الخدش، بل وجهها للمكان المناسب.
            <br>6. يمكن استخدام أغطية سيليكون للأظافر كحل مؤقت.`,
        
          // Dog health and care
          'كيف أعتني بجرو صغير؟':
            `للعناية بالجراء الصغيرة:
            <br>1. التغذية المناسبة: طعام مخصص للجراء.
            <br>2. التطعيمات: اتبع جدول التطعيمات من الطبيب البيطري.
            <br>3. التدريب: ابدأ تدريب الجرو على النظافة مبكراً.
            <br>4. التمارين: وفر وقتاً للعب والنشاط.
            <br>5. النظافة: استحمام دوري وتنظيف الأسنان.
            <br>6. الرعاية الصحية: فحوصات دورية عند الطبيب البيطري.
            <br>7. التنشئة الاجتماعية: عرّض الجرو لمواقف وأشخاص مختلفين.`,
        
          'متى يجب تطعيم الكلاب؟':
            `جدول تطعيمات الكلاب:
            <br>1. 6-8 أسابيع: التطعيم الخماسي الأول
            <br>2. 10-12 أسبوع: التطعيم الخماسي الثاني + تطعيم داء الكلب الأول
            <br>3. 14-16 أسبوع: التطعيم الخماسي الثالث + تطعيم داء الكلب الثاني
            <br>4. سنوياً: جرعات تنشيطية
            <br>يجب استشارة الطبيب البيطري لتحديد الجدول المناسب لكلبك حسب بيئته ومخاطر الأمراض المحلية.`,
        
          'كيف أدرب كلبي على الطاعة؟':
            `لتدريب الكلب على الطاعة:
            <br>1. ابدأ التدريب مبكراً وكن متسقاً في الأوامر.
            <br>2. استخدم التعزيز الإيجابي (المكافآت والمديح) بدلاً من العقاب.
            <br>3. تدرب لفترات قصيرة (5-10 دقائق) عدة مرات يومياً.
            <br>4. ابدأ بأوامر بسيطة مثل "اجلس" و"تعال" و"انتظر".
            <br>5. استخدم إشارات يدوية مع الأوامر الصوتية.
            <br>6. كن صبوراً وتجنب الغضب أو الإحباط.
            <br>7. يمكنك الاستعانة بمدرب محترف للكلاب للمساعدة.`,
        
          'كيف أتعامل مع عدوانية الكلب؟':
            `للتعامل مع عدوانية الكلب:
            <br>1. استشر طبيباً بيطرياً أولاً لاستبعاد أي أسباب صحية للعدوانية.
            <br>2. حدد نوع العدوانية ومسبباتها (خوف، إقليمية، حماية موارد).
            <br>3. تجنب المواقف التي تثير العدوانية مؤقتاً.
            <br>4. لا تعاقب الكلب على السلوك العدواني لأنه قد يزيد المشكلة.
            <br>5. استعن بمدرب سلوكيات محترف أو طبيب بيطري متخصص في السلوكيات.
            <br>6. كن صبوراً، فتعديل السلوك يستغرق وقتاً.
            <br>7. فكر في استخدام أدوية مهدئة بوصفة طبية في الحالات الشديدة.`,
        
          // Nutrition and food
          'ما هي الأطعمة الممنوعة للحيوانات الأليفة؟':
            `الأطعمة الممنوعة للحيوانات الأليفة تشمل:
            <br>1. الشوكولاتة والكافيين.
            <br>2. البصل والثوم.
            <br>3. العنب والزبيب.
            <br>4. المكسرات، خاصة اللوز المر.
            <br>5. الكحول.
            <br>6. الأفوكادو.
            <br>7. منتجات الألبان (لبعض الحيوانات).
            <br>8. العظام المطبوخة (قد تتشظى).
            <br>9. الحلويات الصناعية خاصة التي تحتوي على الزيليتول.
            <br>10. الخميرة والعجين النيء.`,
        
          'ما هو النظام الغذائي المناسب للقطط؟':
            `النظام الغذائي المناسب للقطط:
            <br>1. القطط حيوانات آكلة للحوم بالأساس وتحتاج لبروتين حيواني.
            <br>2. تأكد من أن الطعام يحتوي على التورين (حمض أميني ضروري للقطط).
            <br>3. قدم طعاماً متوازناً عالي الجودة مخصصاً للقطط.
            <br>4. وفر ماءً نظيفاً وطازجاً دائماً.
            <br>5. تجنب إطعام القطط طعام الكلاب لأنه لا يلبي احتياجاتها.
            <br>6. راقب وزن القطة وعدّل كمية الطعام حسب الحاجة.
            <br>7. قس`
        };
        
        // Check if we have a predefined response
        if (responses[userQuery]) {
          return responses[userQuery];
        }
        
        // Default responses for other queries
        const defaultResponses = [
          "شكراً على سؤالك. يمكنك التواصل مع أحد أطبائنا البيطريين للحصول على معلومات أكثر تفصيلاً.",
          "هذا سؤال مهم. أنصحك بزيارة أقرب عيادة بيطرية لدينا للحصول على المساعدة المناسبة.",
          "يمكنك العثور على معلومات إضافية حول هذا الموضوع في قسم المقالات التعليمية على موقعنا.",
          "سأقوم بتوجيه استفسارك إلى فريق الأطباء لدينا وسيتم التواصل معك قريباً.",
          "هذه مسألة تحتاج لتقييم مباشر من طبيب بيطري. يمكنك حجز موعد من خلال تطبيقنا."
        ];
        
        // Return a random default response
        return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
      }
      
      // Function to handle sending a message
      function sendUserMessage() {
        const message = userMessage.value.trim();
        if (message === '') return;
        
        // Add user message to chat
        addMessage(message, true);
        userMessage.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Simulate AI thinking time
        setTimeout(() => {
          removeTypingIndicator();
          
          // Get and add AI response
          const aiResponse = getAIResponse(message);
          addMessage(aiResponse);
        }, 1500);
      }
      
      // Event listeners
      sendMessage.addEventListener('click', sendUserMessage);
      
      userMessage.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          sendUserMessage();
        }
      });
    });
    
    // Function to set a question from suggestions
    function setQuestion(question) {
      document.getElementById('userMessage').value = question;
      document.getElementById('sendMessage').click();
    }
  </script>
</body>
</html>