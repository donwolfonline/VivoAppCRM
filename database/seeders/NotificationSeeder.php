<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationTemplateLangs;
use App\Models\NotificationTemplates;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notifications = [
            'new_holiday'=>'New Holiday',
            'new_meeting'=>'New Meeting',
            'new_event'=>'New Event',
            'new_lead'=>'New Lead',
            'lead_to_deal_conversion'=>'Lead to deal Conversation',
            'new_estimate'=>'New Estimate',
            'new_task_comment'=>'New Task Comment',
            'new_milestone'=>'New Milestone',
            'support_ticket'=>'Support Ticket',
            'new_company_policy'=>'Company Policy',
            'new_award'=>'New Award',
            'new_project'=>'New Project',
            'new_project_status'=>'New Project Status',
            'new_invoice'=>'New Invoice',
            'invoice_status'=>'Invoice Status',
            'new_deal'=>'New Deal',
            'new_task'=>'New Task',
            'task_moved'=>'Task Moved',
            'new_payment'=>'New Payment',
            'new_contract'=>'New Contract',
            'leave_status'=>'Leave Status',
            'new_trip' =>'New Trip'
        ];

        $defaultTemplate = [
                //New Holiday
                'new_holiday' => [
                    'variables' => '{
                        "Date": "date",
                        "Occasion": "occasion"
                        }',
                    'lang' => [
                        'ar' => "التاريخ {date} المناسبة {المناسبة}",
                        'zh' => "日期 {date} 次 { 场合}",
                        'da' =>  'Dato {date} Anledning {occasion}',
                        'de' => 'Datum {Datum} Anlass {Gelegenheit}',
                        'en' => 'Date {date} Occasion {occasion}',
                        'es' => 'Fecha {fecha} Ocasión {ocasión}',
                        'fr' => 'Date {date} Occasion {occasion}',
                        'it' => 'Data {data} Occasione {occasione}',
                        'ja' => '「日付 {date} 行事 {occasion}」',
                        'he' => 'תאריך {date} Occasion {בהזדמנות}',
                        'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
                        'pl' => '„Data {data} Okazja {okazja}”',
                        'ru' => 'Дата {дата} событие {случай}',
                        'pt' => 'Data {data} Ocasião {ocasião}',
                        'tr' => 'Tarih { date } Tarih { vesilesi }',
                        'pt-br' => 'Data {date} Ocasião {ocasião}',
                    ],
                ],
                //New Meeting
                'new_meeting' => [
                    'variables' => '{
                        "Title": "title",
                        "Date": "date"
                        }',
                    'lang' => [
                        'ar' => 'اجتماع جديد {title} في {date}',
                        'zh' => "在 {date} 的新会议 {title }",
                        'da' =>  'Nyt møde {title} den {date}',
                        'de' => 'Neues Meeting {title} am {date}',
                        'en' => 'New Meeting {title} on {date}',
                        'es' => 'Nueva reunión {título} el {fecha}',
                        'fr' => 'Nouvelle réunion {title} le {date}',
                        'it' => 'Nuovo incontro {title} il giorno {date}',
                        'ja' => '{date} の新しい会議 {title}',
                        'he' => 'פגישה חדשה {title} בתאריך {date}',
                        'nl' => 'Nieuwe vergadering {title} op {date}',
                        'pl' => 'Nowe spotkanie {title} w dniu {date}',
                        'ru' => 'Новая встреча {название} {дата}',
                        'pt' => 'Nova reunião {title} em {date}',
                        'tr' => 'Yeni Toplantı { title }, { date } tarihinde',
                        'pt-br' => 'Novo Meeting {título} em {date}',
                    ],
                ],
                //New Event
                'new_event' => [
                    'variables' => '{
                        "Event Title": "event_title",
                        "Department Name": "department_name",
                        "Start Date": "start_date",
                        "End Date": "end_date"
                        }',
                    'lang' => [
                        'ar' => "عنوان الحدث {event_title} Event Department {department_name} Start Date {start_date} End Date {end_date}",
                        'zh' => "事件标题 {event_title } 事件部门 {department_name} 开始日期 {start_date} 结束日期 {end_date}",
                        'da' => 'Begivenhedstitel {event_title} Begivenhedsafdeling {department_name} Startdato {start_date} Slutdato {end_date}',
                        'de' => 'Titel der Veranstaltung {event_title} Abteilung der Veranstaltung {department_name} Startdatum {start_date} Enddatum {end_date}',
                        'en' => 'Event Title {event_title} Event Department {department_name} Start Date {start_date} End Date {end_date}',
                        'es' => 'Título del evento {event_title} Departamento del evento {department_name} Fecha de inicio {start_date} Fecha de finalización {end_date}',
                        'fr' => 'Titre de lévénement {event_title} Service de lévénement {department_name} Date de début {start_date} Date de fin {end_date}',
                        'it' => 'Titolo dellevento {event_title} Reparto evento {department_name} Data di inizio {start_date} Data di fine {end_date}',
                        'ja' => '「イベント タイトル {event_title} イベント部門 {department_name} 開始日 {start_date} 終了日 {end_date}」',
                        'he' => 'כותרת אירוע {event_title} מחלקת אירועים {department_name} תאריך התחלה {start_date} תאריך סיום {end_date}',
                        'nl' => 'Evenementtitel {event_title} Evenementafdeling {department_name} Startdatum {start_date} Einddatum {end_date}',
                        'pl' => '„Tytuł wydarzenia {event_title} Dział wydarzenia {department_name} Data rozpoczęcia {start_date} Data zakończenia {end_date}',
                        'ru' => 'Название мероприятия {event_title} Отдел мероприятия {department_name} Дата начала {start_date} Дата окончания {end_date}',
                        'pt' => 'Título do evento {event_title} Departamento do evento {department_name} Data de início {start_date} Data de término {end_date}',
                        'tr' => 'Olay Başlığı { event_title } Olay Departmanı { department_name } Başlangıç Tarihi { start_date } Bitiş Tarihi { end_date }',
                        'pt-br' => 'Título do evento {event_title} Departamento de eventos {department_name} Data de início {start_date} Data de término {end_date}',
                    ],
                ],
                //New Lead
                'new_lead' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Lead Name": "lead_name",
                        "Lead Email": "lead_email"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء عميل محتمل جديد بواسطة {user_name}',
                        'zh' => "{user_name} 创建的新商机",
                        'da' => 'Neuer Lead erstellt von {user_name}',
                        'de' => 'Ny kundeemne oprettet af {user_name}',
                        'en' => 'New Lead created by {user_name}',
                        'es' => 'Nuevo cliente potencial creado por {user_name}',
                        'fr' => 'Nouveau prospect créé par {user_name}',
                        'it' => 'Nuovo lead creato da {user_name}',
                        'ja' => '{user_name} によって作成された新しいリード',
                        'he' => "ביצוע חדש שנוצר על-ידי {user_name}",
                        'nl' => 'Nieuwe lead gemaakt door {user_name}',
                        'pl' => 'Nowy potencjalny klient utworzony przez użytkownika {user_name}',
                        'ru' => 'Новый интерес создан пользователем {user_name}',
                        'pt' => 'Novo lead criado por {user_name}',
                        'tr' => "{ user_name } tarafından oluşturulan Yeni Lider",
                        'pt-br' => "Novo Lead criado por {user_name}",
                    ]
                ],
                //lead_to_deal_conversion
                'lead_to_deal_conversion' => [
                    'variables' => '{
                        "Company Name": "user_name",
                         "Lead Name": "lead_name",
                        "Lead Email": "lead_email"
                        }',
                    'lang' => [
                        'ar' => 'تم تحويل الصفقة من خلال العميل المحتمل {lead_name}',
                        'zh' => "已通过商机 {lead_name} 进行交易",
                        'da' => 'Aftale konverteret via kundeemne {lead_name}',
                        'de' => 'Geschäftsabschluss durch Lead {lead_name}',
                        'en' => 'Deal converted through lead {lead_name}',
                        'es' => 'Trato convertido a través del cliente potencial {lead_name}',
                        'fr' => 'Offre convertie via le prospect {lead_name}',
                        'it' => 'Offerta convertita tramite il lead {lead_name}',
                        'ja' => 'リード {lead_name} を通じて商談が成立',
                        'he' => "העסקה הומרה באמצעות עופרת {lead_name}",
                        'nl' => 'Deal geconverteerd via lead {lead_name}',
                        'pl' => 'Umowa przekonwertowana przez lead {lead_name}',
                        'ru' => 'Конвертация сделки через лид {lead_name}',
                        'pt' => 'Negócio convertido por meio do lead {lead_name}',
                        'tr' => "Baş { lead_name } ile dönüştürülen anlaşma",
                        'pt-br' => "Acordo convertido através do lead {lead_name}",
                    ]
                ],
                //New Estimate
                'new_estimate' => [
                    'variables' => '{
                        "Company Name": "user_name"
                        }',
                    'lang' => [
                        'ar' =>  'تم إنشاء التقدير الجديد بواسطة {user_name}',
                        'zh' => "由 {user_name} 创建的新估计。",
                        'da' => 'Nyt estimat oprettet af {user_name}',
                        'de' => 'Neue Schätzung erstellt von {user_name}',
                        'en' => 'New Estimation created by the {user_name}.',
                        'es' => 'Nueva estimación creada por {user_name}',
                        'fr' => 'Nouvelle estimation créée par {user_name}',
                        'it' => 'Nuova stima creata da {user_name}',
                        'ja' => '{user_name} によって作成された新しい見積もり',
                        'he' => "הערכה חדשה שנוצרה על-ידי {user_name}.",
                        'nl' => 'Nieuwe schatting gemaakt door de {user_name}',
                        'pl' => 'Nowa prognoza utworzona przez użytkownika {user_name}',
                        'ru' => 'Новая оценка, созданная {user_name}',
                        'pt' => 'Nova estimativa criada por {user_name}',
                        'tr' => "{ user_name } tarafından oluşturulan Yeni Tahmin.",
                        'pt-br' => "Nova Estimativa criada pelo {user_name}.",
                    ]
                ],
                //New Milestone
                'new_milestone' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Title": "title",
                        "Cost": "cost",
                        "Start Date": "start_date",
                        "Due Date": "due_date"
                        }',
                    'lang' => [
                        'ar' => 'تمت إضافة مرحلة هامة جديدة {title} للتكلفة {cost} تاريخ البدء {start_date} وتاريخ الاستحقاق {due_date}',
                        'zh' => "新里程碑添加了成本 {成本} 开始日期 {start_date} 和到期日期 {due_date} 的 {title}",
                        'da' => 'Ny milepæl tilføjet { title } af Cost { cost } Startdato { start_date } og Forfaldsdato { due_date }',
                        'de' => 'Neu hinzugefügter Meilenstein { Titel } ​​der Kosten { Kosten } Startdatum { Startdatum } und Fälligkeitsdatum { Fälligkeitsdatum }',
                        'en' => 'New Milestone added {title} of Cost {cost} Start Date {start_date} and Due Date {due_date}',
                        'es' => 'Se agregó un nuevo hito {título} del costo {cost} fecha de inicio {start_date} y fecha de vencimiento {due_date}',
                        'fr' => 'Nouveau jalon ajouté { title } de Coût { cost } Date de début { start_date } et Date déchéance { due_date }',
                        'it' => 'Nuovo traguardo aggiunto { title } di Costo { cost } Data di inizio { start_date } e Data di scadenza { due_date }',
                        'ja' => '新しいマイルストーンがコスト {cost} の {title} に追加されました 開始日 {start_date} と期日 {due_date}',
                        'he' => "אבן דרך חדשה נוספה {title} של עלות {title} תאריך ההתחלה {start_date} ותאריך היעד {due_date}",
                        'nl' => 'Nieuwe mijlpaal toegevoegd { titel } ​​van kosten { cost } Startdatum { start_date } en vervaldatum { due_date }',
                        'pl' => 'Dodano nowy kamień milowy { tytuł } Koszt { koszt } Data rozpoczęcia { data_początkowa } i Termin { termin_data }',
                        'ru' => 'Добавлен новый этап {название} стоимости {стоимость} Дата начала {start_date} и срок выполнения { due_date}',
                        'pt' => 'Novo marco adicionado { title } de Custo { cost } Data de início { start_date } e Data de vencimento { due_date }',
                        'tr' => "Yeni Aşama { title } Maliyeti { maliyet } Başlangıç Tarihi { start_date } ve Bitiş Tarihi { due_date } eklendi",
                        'pt-br' => "Novo marco adicionado { title } de Custo { cost } Data de início { start_date } e Data de vencimento { due_date }",
                    ]
                ],
                //New support_ticket
                'support_ticket' => [
                    'variables' => '{
                        "Support Priority": "support_priority",
                        "Support User Name": "support_user_name"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء بطاقة دعم جديدة ذات أولوية {support_priority} لـ {support_user_name}',
                        'zh' => "为 {support_user_name} 创建了 { support_priority} 优先级的新支持凭单",
                        'da' => 'Ny supportbillet oprettet med prioritet {support_priority} til {support_user_name}',
                        'de' => 'Neues Support-Ticket mit Priorität {support_priority} für {support_user_name} erstellt',
                        'en' => 'New Support ticket created of {support_priority} priority for {support_user_name}',
                        'es' => 'Nuevo ticket de soporte creado con prioridad {support_priority} para {support_user_name}',
                        'fr' => "Nouveau ticket d'assistance créé avec la priorité {support_priority} pour {support_user_name}",
                        'it' => 'Nuovo ticket di assistenza creato con priorità {support_priority} per {support_user_name}',
                        'ja' => '{support_user_name} の優先度 {support_priority} の新しいサポート チケットが作成されました',
                        'he' => "כרטיס תמיכה חדש שנוצר עבור קדימות {support_priority} עבור {support_user_name}",
                        'nl' => 'Nieuw ondersteuningsticket gemaakt met prioriteit {support_priority} voor {support_user_name}',
                        'pl' => 'Utworzono nowe zgłoszeninew_support_tickete do pomocy technicznej o priorytecie {support_priority} dla użytkownika {support_user_name}',
                        'ru' => 'Создан новый запрос в службу поддержки с приоритетом {support_priority} для {support_user_name}',
                        'pt' => 'Novo tíquete de suporte criado com prioridade {support_priority} para {support_user_name}',
                        'tr' => "{ support_user_name } için { support_priority } önceliğine ilişkin yeni Destek bileti oluşturuldu",
                        'pt-br' => "Novo tíquete de suporte criado com prioridade {support_priority} para {support_user_name}",
                    ]
                ],
                //New Task Comment
                'new_task_comment' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Task Name": "task_name",
                        "Project Name": "project_name"
                        }',
                    'lang' => [
                        'ar' => 'تمت إضافة تعليق جديد في المهمة {task_name} للمشروع {project_name} بواسطة {user_name}',
                        'zh' => "{user_name} 在项目 {project_name } 的任务 {task_name} 中添加了新注释",
                        'da' => 'Ny kommentar tilføjet til opgave {task_name} i projektet {project_name} af {user_name}',
                        'de' => 'Neuer Kommentar in Aufgabe {task_name} des Projekts {project_name} von {user_name} hinzugefügt',
                        'en' => 'New Comment added in task {task_name} of project {project_name} by {user_name}',
                        'es' => 'Nuevo comentario agregado en la tarea {task_name} del proyecto {project_name} por {user_name}',
                        'fr' => 'Nouveau commentaire ajouté dans la tâche {task_name} du projet {project_name} par {user_name}',
                        'it' => 'Nuovo commento aggiunto nell attività {task_name} del progetto {project_name} da {user_name}',
                        'ja' => 'プロジェクト {project_name} のタスク {task_name} に {user_name} によって新しいコメントが追加されました',
                        'he' => "הערה חדשה נוספה במשימה {task_name} של הפרויקט {project_name} לפי {user_name}",
                        'nl' => 'Nieuwe opmerking toegevoegd in taak {task_name} van project {project_name} door {user_name}',
                        'pl' => 'Nowy komentarz dodany w zadaniu {task_name} projektu {project_name} przez {user_name}',
                        'ru' => 'Новый комментарий добавлен в задачу {task_name} проекта {project_name} пользователем {user_name}',
                        'pt' => 'Novo comentário adicionado na tarefa {task_name} do projeto {project_name} por {user_name}',
                        'tr' => "{ user_name } tarafından { projec_name } adlı projenin { task_name } görevine yeni yorum eklendi",
                        'pt-br' => "Novo comentário adicionado na tarefa {task_name} do projeto {project_name} por {user_name}",
                    ]
                ],
                //New Company Policy
                'new_company_policy' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Company Policy Name": "company_policy_name"
                        }',
                    'lang' => [
                        'ar' => 'سياسة {company_policy_name} التي أنشأها {user_name}',
                        'zh' => "{company_policy_name} 策略由 {user_name} 创建",
                        'da' => '{company_policy_name}-politik oprettet af {user_name}',
                        'de' => 'Richtlinie {company_policy_name} erstellt von {user_name}',
                        'en' => '{company_policy_name} policy created by {user_name}',
                        'es' => 'Política {company_policy_name} para la sucursal {user_name} creada',
                        'fr' => 'Stratégie {company_policy_name} créée par {user_name}',
                        'it' => 'norma di {company_policy_name} creata da {user_name}',
                        'ja' => '{user_name} によって作成された {company_policy_name} ポリシー',
                        'he' => "המדיניות {company_policy_name נוצרה על ידי {user_name}",
                        'nl' => 'Beleid {company_policy_name} gemaakt door {user_name}',
                        'pl' => 'Zasady firmy {company_policy_name} utworzone przez użytkownika {user_name}',
                        'ru' => 'Создана политика {company_policy_name} для филиала {user_name}',
                        'pt' => 'Política {company_policy_name} criada por {user_name}',
                        'tr' => "{ company_policy_name } ilkesi, { user_name } tarafından oluşturuldu",
                        'pt-br' => "Política {company_policy_name} criada por {user_name}",
                    ]
                ],
                //New Award
                'new_award' => [
                    'variables' => '{
                        "Award Name": "award_name",
                        "Employee Name": "employee_name",
                        "Award Date": "award_date"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء {award_name} لـ {employee_name} من {Award_date}',
                        'zh' => "已从 {award_date} 为 {employe_name} 创建 {award_name}",
                        'da' => '{award_name} oprettet til {employee_name} fra {award_date}',
                        'de' => '{award_name} erstellt für {employee_name} vom {award_date}',
                        'en' => '{award_name} created for {employee_name} from {award_date}',
                        'es' => '{award_name} creado para {employee_name} de {award_date}',
                        'fr' => '{award_name} créé pour {employee_name} à partir du {award_date}',
                        'it' => '{award_name} creato per {employee_name} da {award_date}',
                        'ja' => '{employee_name} のために {award_name} が {award_date} から作成されました',
                        'he' => "{award_name} שנוצר עבור {העובד ee_name} מ - {award_date}",
                        'nl' => '{award_name} gemaakt voor {employee_name} vanaf {award_date}',
                        'pl' => '{award_name} utworzone dla {employee_name} od {award_date}',
                        'ru' => '{award_name} создано для {employee_name} с {award_date}',
                        'pt' => '{award_name} criado para {employee_name} de {award_date}',
                        'tr' => "{ employee_name } için { award_date } içinden { award_name } oluşturuldu",
                        'pt-br' => "{award_name} criado para {employee_name} de {award_date}",
                    ]
                ],
                //New Project
                'new_project' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Project Name": "project_name"
                        }',
                    'lang' => [
                        'ar' => 'تم تكوين مشروع جديد { project_name } بواسطة { user_name }',
                        'zh' => "{user_name} 创建了新的 {project_name} 项目。",
                        'da' => 'Nyt { project_name } projekt oprettet af { user_name }',
                        'de' => 'Neues Projekt {project_name} erstellt von {user_name}',
                        'en' => 'New {project_name} project created by {user_name}.',
                        'es' => 'Nuevo proyecto {project_name} creado por {user_name}',
                        'fr' => 'Nouveau projet { project_name } créé par { nom_utilisateur }',
                        'it' => 'Nuovo progetto {project_name} creato da {user_name}',
                        'ja' => '{user_name} によって作成された新規 {project_name} プロジェクト',
                        'he' => "פרויקט {project_name} חדש שנוצר על ידי {user_name}.",
                        'nl' => 'Nieuw project { project_name } gemaakt door { user_name }',
                        'pl' => 'Nowy projekt {project_name } utworzony przez użytkownika {user_name }',
                        'ru' => 'Новый проект { project_name }, созданный пользователем { user_name }',
                        'pt' => 'Novo projeto {project_name} criado por {user_name}',
                        'tr' => "{ user_name } tarafından oluşturulan yeni { project_name } projesi.",
                        'pt-br' => "Novo projeto {project_name} criado por {user_name}",
                    ]
                ],
                //New Project status
                'new_project_status' => [
                    'variables' => '{
                         "Project Name": "project_name",
                         "Status": "status"

                        }',
                    'lang' => [
                        'ar' =>  'تم تحديث حالة {project_name} الجديدة {status} بنجاح',
                        'zh' => "已成功更新 {project_name} 状态更新 {status} 。",
                        'da' => 'Ny {project_name}-status blev opdateret {status}',
                        'de' => 'Neuer Status {project_name} {Status} erfolgreich aktualisiert',
                        'en' => 'New {project_name} Status Updadated {status} successfully.',
                        'es' => 'Nuevo estado de {project_name} actualizado {status} con éxito',
                        'fr' => 'Nouveau statut de {project_name} {status} mis à jour avec succès',
                        'it' => 'Nuovo stato {project_name} Aggiornato {status} con successo',
                        'ja' => '新しい {project_name} ステータス {status} が正常に更新されました',
                        'he' => "מצב חדש של {project_name} עודכן {status} בהצלחה.",
                        'nl' => 'Nieuwe {project_name}-status {status} succesvol bijgewerkt',
                        'pl' => 'Nowy status {project_name} Zaktualizowano {status} pomyślnie',
                        'ru' => 'Новый статус {project_name} успешно обновлен {статус}',
                        'pt' => 'Novo status {project_name} atualizado {status} com sucesso',
                        'tr' => "Yeni { project_name } Durumu Updated { status } durumunu başarıyla yükseltti.",
                        'pt-br' => "Novo status {project_name} atualizado {status} com sucesso",
                    ]
                ],
                //New Invoice
                'new_invoice' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Invoice Number": "invoice_number"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء الفاتورة الجديدة {invoice_number} بواسطة {user_name}',
                        'zh' => "{user_name} 创建的新发票 { invoice_number }",
                        'da' => 'Ny faktura {invoice_number} oprettet af {user_name}',
                        'de' => 'Neue Rechnung {invoice_number} erstellt von {user_name}',
                        'en' => 'New Invoice { invoice_number } created by {user_name}',
                        'es' => 'Nueva factura {invoice_number} creada por {user_name}',
                        'fr' => 'Nouvelle facture {invoice_number} créée par {user_name}',
                        'it' => 'Nuova fattura {invoice_number} creata da {user_name}',
                        'ja' => '{user_name} によって作成された新しい請求書 {invoice_number}',
                        'he' => "חשבונית חדשה {invoice_number} נוצרה על-ידי {user_name}",
                        'nl' => 'Nieuwe factuur {invoice_number} gemaakt door {user_name}',
                        'pl' => 'Nowa faktura {invoice_number} utworzona przez użytkownika {user_name}',
                        'ru' => 'Новый счет {invoice_number}, созданный {user_name}',
                        'pt' => 'Nova fatura {invoice_number} criada por {user_name}',
                        'tr' => "Yeni Fatura { invoice_number }, { user_name } tarafından oluşturuldu",
                        'pt-br' => "Nova fatura {invoice_number} criada por {user_name}",
                    ]
                ],
                'invoice_status' => [
                    'variables' => '{
                        "Invoice": "invoice",
                        "Old status": "old_status",
                        "New Status": "status"
                         }',
                    'lang' => [
                        'ar' => 'تم تغيير حالة الفاتورة {الفاتورة} من {old_status} إلى {status}',
                        'zh' => "发票 {发票} 状态已从 {old_status} 更改为 {status}",
                        'da' => 'Faktura {invoice}-status ændret fra {old_status} til {status}',
                        'de' => 'Status der Rechnung {invoice} von {old_status} in {status} geändert',
                        'en' => 'Invoice {invoice} status changed from {old_status} to {status}',
                        'es' => 'El estado de la factura {factura} cambió de {old_status} a {status}',
                        'fr' => 'Le statut de la facture {invoice} est passé de {old_status} à {status}',
                        'it' => 'Lo stato della fattura {invoice} è cambiato da {old_status} a {status}',
                        'ja' => '請求書 {invoice} のステータスが {old_status} から {status} に変更されました',
                        'he' => "חשבונית {חשבונית} סטאטוס השתנה מ - {old_status} ל - {status}",
                        'nl' => 'Factuur {factuur} status gewijzigd van {old_status} in {status}',
                        'pl' => 'Faktura {invoice} zmieniła stan z {old_status} na {status}',
                        'ru' => 'Статус счета-фактуры {invoice} изменен с {old_status} на {status}',
                        'pt' => 'Status da fatura {invoice} alterado de {old_status} para {status}',
                        'tr' => "Fatura { fatura } durumu, { old_status } durumundan { status } durumuna değiştirildi",
                        'pt-br' => "Status da fatura {invoice} alterado de {old_status} para {status}",
                    ]
                ],
                'new_deal' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Deal Name": "deal_name"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء الصفقة الجديدة بواسطة {user_name}',
                        'zh' => "{user_name} 创建的新政",
                        'da' => 'Ny aftale oprettet af {user_name}',
                        'de' => 'Neuer Deal erstellt von {user_name}',
                        'en' => 'New Deal created by {user_name}',
                        'es' => 'Nueva oferta creada por {user_name}',
                        'fr' => 'Nouvelle offre créée par {user_name}',
                        'it' => 'New Deal creato da {user_name}',
                        'ja' => '{user_name} によって作成された新しいディール',
                        'he' => "עסקה חדשה שנוצרה על-ידי {user_name}",
                        'nl' => 'Nieuwe deal gemaakt door {user_name}',
                        'pl' => 'Nowa oferta utworzona przez użytkownika {user_name}',
                        'ru' => 'Новая сделка создана пользователем {user_name}',
                        'pt' => 'Novo negócio criado por {user_name}',
                        'tr' => "{ user_name } tarafından oluşturulan Yeni Anlaşma",
                        'pt-br' => "Novo negócio criado por {user_name}",
                    ]
                ],
                //New Task
                'new_task' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Task Name": "task_name",
                        "Project Name": "project_name"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء مهمة {task_name} لمشروع {project_name} بواسطة {user_name}',
                        'zh' => "{user_name} 为 {project_name} 项目创建 {task_name} 任务。",
                        'da' => '{task_name} opgave oprettet for {project_name}-projekt af {user_name}',
                        'de' => 'Aufgabe {task_name} erstellt für Projekt {project_name} von {user_name}',
                        'en' => '{task_name} task create for {project_name} project by {user_name}.',
                        'es' => '{task_name} tarea creada para {project_name} proyecto por {user_name}',
                        'fr' => 'Tâche {task_name} créée pour le projet {project_name} par {user_name}',
                        'it' => 'Attività {task_name} creata per il progetto {project_name} da {user_name}',
                        'ja' => '{user_name} による {project_name} プロジェクトの {task_name} タスク作成',
                        'he' => "המשימה {task_name} יוצרת עבור פרויקט {project_name} על-ידי {user_name}.",
                        'nl' => '{task_name} taak gemaakt voor {project_name} project door {user_name}',
                        'pl' => 'Zadanie {task_name} utworzono dla projektu {project_name} przez użytkownika {user_name}',
                        'ru' => 'Задача {task_name} создана для проекта {project_name} пользователем {user_name}',
                        'pt' => 'Tarefa {task_name} criada para o projeto {project_name} por {user_name}',
                        'tr' => "{ subject_name } için { task_name } görevi, { user_name } tarafından { projec_adı } projesi için oluşturma.",
                        'pt-br' => "Tarefa {task_name} criada para o projeto {project_name} por {user_name}",
                    ]
                ],
                //Task Moved
                'task_moved' => [
                    'variables' => '{
                        "Task Title": "task_title",
                        "Old Task Stages": "task_stage",
                        "New Task Stages": "new_task_stage"
                        }',
                    'lang' => [
                        'ar' => 'المهمة {task_title} تغيير المرحلة من {task_stage} إلى {new_task_stage}',
                        'zh' => "任务 {task_title } 阶段从 {task_stage} 更改为 {new_task_stage}",
                        'da' => 'Opgave {task_title} Faseændring fra {task_stage} til {new_task_stage}',
                        'de' => 'Aufgabe {task_title} Phasenwechsel von {task_stage} zu {new_task_stage}',
                        'en' => 'Task {task_title} Stage change from {task_stage} to {new_task_stage}',
                        'es' => 'Tarea {task_title} Cambio de etapa de {task_stage} a {new_task_stage}',
                        'fr' => 'Tâche {task_title} Changement détape de {task_stage} à {new_task_stage}',
                        'it' => 'Attività {task_title} Cambio fase da {task_stage} a {new_task_stage}',
                        'ja' => 'タスク {task_title} ステージが {task_stage} から {new_task_stage} に変更されました',
                        'he' => "משימה {task_title} שינוי שלב מ - {task_השלב} עד {new_task_השלב}",
                        'nl' => 'Taak {task_title} Stage verandering van {task_stage} naar {new_task_stage}',
                        'pl' => 'Zmiana etapu zadania {task_title} z {task_stage} na {new_task_stage}',
                        'ru' => 'Стадия задачи {task_title} изменена с {task_stage} на {new_task_stage}',
                        'pt' => 'Mudança de estágio da tarefa {task_title} de {task_stage} para {new_task_stage}',
                        'tr' => "Görev { task_title } { task_stage } olan aşama değişikliği { new_task_stage } olarak değiştiriliyor",
                        'pt-br' => "Mudança de estágio da tarefa {task_title} de {task_stage} para {new_task_stage}",
                    ]
                ],
                //Task Moved
                'new_payment' => [
                    'variables' => '{
                        "User Name": "user_name",
                        "Amount": "amount",
                        "Created By": "created_by"
                         }',
                    'lang' => [
                        'ar' => 'تم إنشاء دفعة جديدة بمبلغ {amount} من أجل {user_name} بواسطة {created_by}',
                        'zh' => "{ created_by} 创建的 {user_name} 的新付款 { 金额}",
                        'da' => 'Ny betaling på {amount} oprettet for {user_name} oprettet af {created_by}',
                        'de' => 'Neue Zahlung in Höhe von {amount} erstellt für {user_name} Erstellt von {created_by}',
                        'en' => 'New payment of {amount} created for {user_name} Created By {created_by}',
                        'es' => 'Nuevo pago de {amount} creado para {user_name} Creado por {created_by}',
                        'fr' => 'Nouveau paiement de {amount} créé pour {user_name} Créé par {created_by}',
                        'it' => 'Nuovo pagamento di {amount} creato per {user_name} creato da {created_by}',
                        'ja' => '{user_name} のために作成された {amount} の新しい支払い {created_by} によって作成されました',
                        'he' => "תשלום חדש של {מאונט} שנוצר עבור {user_name} נוצר על ידי {created_by}",
                        'nl' => 'Nieuwe betaling van {amount} gemaakt voor {user_name} Gemaakt door {created_by}',
                        'pl' => 'Nowa płatność w wysokości {amount} została utworzona dla użytkownika {user_name} Utworzona przez {created_by}',
                        'ru' => 'Создан новый платеж на {сумму} для {user_name} Создано {created_by}',
                        'pt' => 'Novo pagamento de {amount} criado para {user_name} Criado por {created_by}',
                        'tr' => "{ created_by } tarafından yaratılan { user_name } için oluşturulan { amount } yeni ödeme",
                        'pt-br' => "Novo pagamento de {amount} criado para {user_name} Criado por {created_by}",
                    ]
                ],
                //New Contract
                'new_contract' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "Contract Name": "contract_subject",
                        "Client Name": "contract_client",
                        "Contract Price": "contract_value",
                        "Contract Start Date": "contract_start_date",
                        "Contract End Date": "contract_end_date"
                        }',
                    'lang' => [
                        'ar' => 'تم إنشاء عقد {Contract_subject} لـ {contract_client} بواسطة {user_name}',
                        'zh' => "{contract_subject } 合同已由 {user_name} 创建 { contract_client}",
                        'da' => '{contract_subject} kontrakt oprettet for {contract_client} af {user_name}',
                        'de' => '{contract_subject} Vertrag erstellt für {contract_client} von {user_name}',
                        'en' => '{contract_subject} contract created for {contract_client} by {user_name}',
                        'es' => '{contract_subject} contrato creado para {contract_client} por {user_name}',
                        'fr' => 'Contrat {contract_subject} créé pour {contract_client} par {user_name}',
                        'it' => 'Contratto {contract_subject} creato per {contract_client} da {user_name}',
                        'ja' => '{user_name} によって {contract_client} のために作成された {contract_subject} 契約',
                        'he' => "{contract_subject} חוזה שנוצר עבור {contract_client} על-ידי {user_name}",
                        'nl' => '{contract_subject} contract gemaakt voor {contract_client} door {user_name}',
                        'pl' => 'Umowa {contract_subject} utworzona dla {contract_client} przez {user_name}',
                        'ru' => 'Контракт {contract_subject} создан для {contract_client} пользователем {user_name}',
                        'pt' => 'Contrato {contract_subject} criado para {contract_client} por {user_name}',
                        'tr' => "{ user_name } tarafından { contract_client } için { contract_subject } sözleşmesi oluşturuldu",
                        'pt-br' => "Contrato {contract_subject} criado para {contract_client} por {user_name}",
                    ]
                ],
                // /leave_status
                'leave_status' => [
                    'variables' => '{
                        "Company Name": "user_name",
                        "status": "status"
                        }',
                    'lang' => [
                        'ar' => 'كانت المغادرة {status} بواسطة {user_name}',
                        'zh' => "{ user_name} 已离开 {status}",
                        'da' => 'Orlov har været {status} af {user_name}',
                        'de' => 'Verlassen wurde {status} von {user_name}',
                        'en' => 'Leave has been {status} by {user_name}',
                        'es' => 'La licencia ha sido {status} por {user_name}',
                        'fr' => 'Le congé a été {status} par {user_name}',
                        'it' => 'Il congedo è stato {status} di {user_name}',
                        'ja' => '{user_name} さんによる {status} の休暇',
                        'he' => "השאירו כבר {status} על ידי {user_name}",
                        'nl' => 'Verlof is {status} door {user_name}',
                        'pl' => 'Urlop został {status} przez {user_name}',
                        'ru' => 'Выход был {status} от {user_name}',
                        'pt' => 'A saída foi {status} de {user_name}',
                        'tr' => "{ user_name } tarafından { status } durumu oluştu",
                        'pt-br' => "A saída foi {status} de {user_name}",
                    ]
                ],
                //new_trip
                'new_trip' => [
                    'variables' => '{
                        "Purpose Of Visit": "purpose_of_visit",
                        "Place Of Visit": "place_of_visit",
                        "Start Date": "start_date",
                        "End Date": "end_date"
                        }',
                    'lang' => [
                        'ar' => 'يبدأ مكان الزيارة الجديد في {place_of_visit} لغرض {الغرض_من_فيزيت} من {start_date} إلى {end_date}',
                        'zh' => "目的 {purpose_of_查访} 的 {place_of_查访} 的新访问地点从 {start_date} 开始到 {end_date}",
                        'da' => 'Nyt besøgssted på {place_of_visit} til formålet {purpose_of_visit} start fra {start_date} til {end_date}',
                        'de' => 'Neuer Besuchsort in {place_of_visit} für den Zweck {purpose_of_visit} von {start_date} bis {end_date}',
                        'en' => 'New Place of visit at {place_of_visit} for purpose {purpose_of_visit} start from {start_date} to {end_date}',
                        'es' => 'Nuevo lugar de visita en {place_of_visit} para el propósito {purpose_of_visit} desde {start_date} hasta {end_date}',
                        'fr' => 'Nouveau lieu de visite à {place_of_visit} dans le but {purpose_of_visit} à partir du {start_date} jusqu au {end_date}',
                        'it' => 'Nuovo luogo di visita a {place_of_visit} per lo scopo {purpose_of_visit} a partire dal {start_date} al {end_date}',
                        'ja' => '{place_of_visit} での目的 {purpose_of_visit} の新しい訪問場所は {start_date} から {end_date} までです',
                        'he' => "מקום חדש של ביקור ב - {place_of_הביקור} עבור המטרה {במכוון _of_הביקור} התחלה מ - {start_date} עד {end_date}",
                        'nl' => 'Nieuwe plaats van bezoek op {place_of_visit} voor doel {purpose_of_visit} start van {start_date} tot {end_date}',
                        'pl' => 'Nowe miejsce wizyty w {place_of_visit} w celu {purpose_of_visit} rozpoczyna się od {start_date} do {end_date}',
                        'ru' => 'Новое место посещения в {place_of_visit} с целью {цель_посещения}, начало с {start_date} по {end_date}',
                        'pt' => 'Novo local de visita em {place_of_visit} para o propósito {purpose_of_visit} começa de {start_date} a {end_date}',
                        'tr' => "{ purpose_of_visit } amacının { start_date } tarihinden { end_date } tarihine kadar başlaması için { place_of_visit } adlı yeni ziyaret yeri",
                        'pt-br' => "Novo local de visita em {place_of_visit} para o propósito {purpose_of_visit} começa de {start_date} a {end_date}",
                    ]
                ],
            ];

        $user = User::where('type','super admin')->first();
        foreach($notifications as $k => $n)
        {
            $ntfy = NotificationTemplates::where('slug',$k)->count();
            if($ntfy == 0)
            {
                $new = new NotificationTemplates();
                $new->name = $n;
                $new->slug = $k;
                $new->save();

                foreach($defaultTemplate[$k]['lang'] as $lang => $content)
                {
                    NotificationTemplateLangs::create(
                        [
                            'parent_id' => $new->id,
                            'lang' => $lang,
                            'variables' => $defaultTemplate[$k]['variables'],
                            'content' => $content,
                            'created_by' => !empty($user) ? $user->id : 1,
                        ]
                    );
                }
            }
        }

    }
}
