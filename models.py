# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models


class Activity(models.Model):
    contact_id = models.IntegerField()
    user_id = models.IntegerField()
    adate = models.DateTimeField(blank=True, null=True)
    type = models.IntegerField(blank=True, null=True)
    field1 = models.TextField(blank=True, null=True)
    field2 = models.TextField(blank=True, null=True)
    content = models.TextField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'activity'


class AddressFormat(models.Model):
    address_format_id = models.AutoField(primary_key=True)
    address_format = models.CharField(max_length=128)
    address_summary = models.CharField(max_length=48)

    class Meta:
        managed = False
        db_table = 'address_format'


class AppnotchUsers(models.Model):
    auid = models.AutoField(primary_key=True)
    ref_user_type = models.CharField(max_length=30)
    ref_user_id = models.IntegerField()
    tenant_id = models.IntegerField()
    tenant_branch_url = models.CharField(max_length=200)
    tenantmember_id = models.IntegerField()
    tenantmember_email = models.CharField(max_length=200)
    tenantmember_password = models.CharField(max_length=64)
    tenantmember_name = models.CharField(max_length=200)
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'appnotch_users'
        unique_together = (('tenantmember_id', 'tenantmember_email'),)


class AqCategory(models.Model):
    category_id = models.SmallAutoField(primary_key=True)
    category_name = models.TextField()
    date_added = models.DateTimeField(blank=True, null=True)
    type = models.IntegerField()
    description = models.TextField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'aq_category'


class AutoEmails(models.Model):
    auto_emails_id = models.AutoField(primary_key=True)
    email = models.CharField(max_length=255)
    subject = models.CharField(max_length=255)
    body = models.TextField(blank=True, null=True)
    footer = models.TextField(blank=True, null=True)
    days = models.IntegerField()
    orders_status_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'auto_emails'


class AutoEmailsToOrdersStatusHistory(models.Model):
    auto_emails_id = models.IntegerField()
    orders_status_history_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'auto_emails_to_orders_status_history'


class Banners(models.Model):
    banners_id = models.AutoField(primary_key=True)
    banners_title = models.CharField(max_length=64)
    banners_url = models.CharField(max_length=64)
    banners_image = models.CharField(max_length=64)
    banners_group = models.CharField(max_length=10)
    banners_viewed_by = models.CharField(max_length=10)
    banners_html_text = models.TextField(blank=True, null=True)
    expires_impressions = models.IntegerField(blank=True, null=True)
    expires_date = models.DateTimeField(blank=True, null=True)
    date_scheduled = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()
    date_status_change = models.DateTimeField(blank=True, null=True)
    status = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'banners'


class BannersHistory(models.Model):
    banners_history_id = models.AutoField(primary_key=True)
    banners_id = models.IntegerField()
    banners_shown = models.IntegerField()
    banners_clicked = models.IntegerField()
    banners_history_date = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'banners_history'


class Categories(models.Model):
    categories_id = models.AutoField(primary_key=True)
    categories_image = models.CharField(max_length=64, blank=True, null=True)
    parent_id = models.IntegerField()
    sort_order = models.IntegerField(blank=True, null=True)
    date_added = models.DateTimeField(blank=True, null=True)
    last_modified = models.DateTimeField(blank=True, null=True)
    categories_type = models.CharField(max_length=1)
    discount_value = models.DecimalField(max_digits=3, decimal_places=2)
    discount_enabled = models.IntegerField()
    mem = models.CharField(max_length=1, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'categories'


class CategoriesDescription(models.Model):
    categories_id = models.IntegerField(primary_key=True)
    categories_name = models.CharField(max_length=32)

    class Meta:
        managed = False
        db_table = 'categories_description'


class City(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'city'


class Company(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'company'


class Competitors(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'competitors'


class Configuration(models.Model):
    configuration_id = models.AutoField(primary_key=True)
    configuration_title = models.CharField(max_length=64)
    configuration_key = models.CharField(max_length=64)
    configuration_value = models.CharField(max_length=255)
    configuration_description = models.CharField(max_length=255)
    configuration_group_id = models.IntegerField()
    sort_order = models.IntegerField(blank=True, null=True)
    last_modified = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()
    use_function = models.CharField(max_length=255, blank=True, null=True)
    set_function = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'configuration'


class ConfigurationGroup(models.Model):
    configuration_group_id = models.AutoField(primary_key=True)
    configuration_group_title = models.CharField(max_length=64)
    configuration_group_description = models.CharField(max_length=255)
    sort_order = models.IntegerField(blank=True, null=True)
    visible = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'configuration_group'


class Contact(models.Model):
    aid = models.PositiveIntegerField()
    cid = models.PositiveIntegerField()
    first_name = models.CharField(max_length=40, blank=True, null=True)
    last_name = models.CharField(max_length=40, blank=True, null=True)
    day_phone = models.CharField(max_length=40, blank=True, null=True)
    evening_phone = models.CharField(max_length=40, blank=True, null=True)
    email = models.CharField(max_length=40, blank=True, null=True)
    city = models.CharField(max_length=40, blank=True, null=True)
    state = models.CharField(max_length=2, blank=True, null=True)
    credit_checking = models.CharField(max_length=1)
    status = models.CharField(max_length=1)
    date = models.DateTimeField()
    reason = models.PositiveIntegerField()
    other_reason = models.TextField()
    back_reason = models.PositiveIntegerField()
    bttc = models.CharField(max_length=50, blank=True, null=True)
    street = models.CharField(max_length=50, blank=True, null=True)
    zip = models.CharField(max_length=10, blank=True, null=True)
    notes = models.TextField(blank=True, null=True)
    comments = models.TextField(blank=True, null=True)
    legal_issue1 = models.CharField(max_length=200, blank=True, null=True)
    legal_issue2 = models.CharField(max_length=200, blank=True, null=True)
    web_form = models.CharField(max_length=1)
    cc_type = models.CharField(max_length=1)
    cc_expire = models.DateField()
    age = models.CharField(max_length=1)
    form_name = models.CharField(max_length=50)

    class Meta:
        managed = False
        db_table = 'contact'


class ContactNoteFiles(models.Model):
    file_id = models.AutoField(primary_key=True)
    owner_id = models.IntegerField(blank=True, null=True)
    contact_id = models.IntegerField(blank=True, null=True)
    file_name = models.TextField(blank=True, null=True)
    note_date = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'contact_note_files'


class Contacts(models.Model):
    owner_id = models.IntegerField()
    group_id = models.IntegerField()
    name = models.CharField(max_length=40)
    salutation = models.CharField(max_length=40)
    company = models.CharField(max_length=60)
    title = models.CharField(max_length=40)
    department = models.CharField(max_length=40)
    address = models.CharField(max_length=100)
    address2 = models.CharField(max_length=100)
    address3 = models.CharField(max_length=100)
    city = models.CharField(max_length=100)
    state = models.CharField(max_length=40)
    zip = models.CharField(max_length=15)
    country = models.CharField(max_length=40)
    phone = models.CharField(max_length=40)
    phone2 = models.CharField(max_length=40)
    extension2 = models.CharField(max_length=10)
    prospect_source = models.CharField(max_length=100)
    extension = models.CharField(max_length=10)
    fax = models.CharField(max_length=40)
    email = models.CharField(max_length=100)
    website = models.CharField(max_length=120)
    status = models.CharField(max_length=1)
    last_result = models.CharField(max_length=100, blank=True, null=True)
    ticker_symbol = models.CharField(max_length=20, blank=True, null=True)
    rating = models.IntegerField()
    insert_date = models.DateField()
    update_date = models.DateField(blank=True, null=True)
    activity_date = models.DateField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'contacts'


class CopyTblMemberContactList(models.Model):
    int_member_contact_list_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    str_member_contact_list = models.TextField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'copy_tbl_member_contact_list'


class Counter(models.Model):
    startdate = models.CharField(max_length=8, blank=True, null=True)
    counter = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'counter'


class CounterHistory(models.Model):
    month = models.CharField(max_length=8, blank=True, null=True)
    counter = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'counter_history'


class Country(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'country'


class CsvFiles(models.Model):
    id = models.SmallAutoField(primary_key=True)
    name = models.CharField(max_length=100, blank=True, null=True)
    date = models.DateTimeField(blank=True, null=True)
    type = models.CharField(max_length=4, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'csv_files'


class CsvParser(models.Model):
    member_id = models.CharField(max_length=32)
    order_id = models.IntegerField()
    invoice_date = models.DateField()
    item = models.CharField(max_length=22)
    price = models.DecimalField(max_digits=10, decimal_places=2)
    percentage = models.FloatField()

    class Meta:
        managed = False
        db_table = 'csv_parser'


class Currencies(models.Model):
    currencies_id = models.AutoField(primary_key=True)
    title = models.CharField(max_length=32)
    code = models.CharField(max_length=3)
    symbol_left = models.CharField(max_length=12, blank=True, null=True)
    symbol_right = models.CharField(max_length=12, blank=True, null=True)
    decimal_point = models.CharField(max_length=1, blank=True, null=True)
    thousands_point = models.CharField(max_length=1, blank=True, null=True)
    decimal_places = models.CharField(max_length=1, blank=True, null=True)
    value = models.FloatField(blank=True, null=True)
    last_updated = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'currencies'


class CustomersBasket(models.Model):
    customers_basket_id = models.AutoField(primary_key=True)
    customers_id = models.IntegerField()
    products_id = models.TextField()
    customers_basket_quantity = models.IntegerField()
    final_price = models.DecimalField(max_digits=6, decimal_places=2)
    customers_basket_date_added = models.CharField(max_length=8, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'customers_basket'


class CustomersBasketAttributes(models.Model):
    customers_basket_attributes_id = models.AutoField(primary_key=True)
    customers_id = models.IntegerField()
    products_id = models.TextField()
    products_options_id = models.IntegerField()
    products_options_value_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'customers_basket_attributes'


class CustomersDiscountGroup(models.Model):
    customers_id = models.SmallIntegerField(primary_key=True)
    discount_group_id = models.SmallIntegerField()
    customers_discount_group_status = models.IntegerField()
    date_added = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'customers_discount_group'


class CustomersEmails(models.Model):
    customers_id = models.PositiveIntegerField()
    customers_email = models.CharField(max_length=128)
    subject = models.CharField(max_length=250)
    dt = models.DateTimeField(blank=True, null=True)
    body = models.TextField()
    query = models.TextField()
    newsletter_id = models.PositiveIntegerField()
    status = models.CharField(max_length=1)
    admin_user = models.CharField(max_length=30)
    id2 = models.PositiveIntegerField()
    sent = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'customers_emails'


class CustomersInfo(models.Model):
    customers_info_id = models.IntegerField(primary_key=True)
    customers_info_date_of_last_logon = models.DateTimeField(blank=True, null=True)
    customers_info_number_of_logons = models.IntegerField(blank=True, null=True)
    customers_info_date_account_created = models.DateTimeField(blank=True, null=True)
    customers_info_date_account_last_modified = models.DateTimeField(blank=True, null=True)
    global_product_notifications = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'customers_info'


class Department(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'department'


class DiscountGroup(models.Model):
    discount_group_id = models.SmallAutoField(primary_key=True)
    discount_group_name = models.CharField(max_length=50)
    discount_group_discount = models.DecimalField(max_digits=4, decimal_places=2)
    date_added = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'discount_group'


class Distributor(models.Model):
    distributor_id = models.AutoField(primary_key=True)
    distributor_title = models.CharField(max_length=255)
    distributor_desc = models.TextField()
    distributor_url = models.CharField(max_length=255)
    distributor_status = models.IntegerField()
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'distributor'


class Emails(models.Model):
    amico_id = models.CharField(primary_key=True, max_length=222)
    customers_email_address = models.CharField(max_length=96)

    class Meta:
        managed = False
        db_table = 'emails'


class ExtraBCustomers(models.Model):
    field_id = models.PositiveIntegerField()
    customers_id = models.PositiveIntegerField()
    val = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'extra_b_customers'


class ExtraBNotes(models.Model):
    id = models.AutoField(unique=True)
    customers_id = models.IntegerField(blank=True, null=True)
    date = models.CharField(max_length=255)
    item_id = models.CharField(max_length=255)
    skoeinvoice = models.IntegerField(db_column='SKOEInvoice', blank=True, null=True)  # Field name made lowercase.
    notes = models.TextField()

    class Meta:
        managed = False
        db_table = 'extra_b_notes'


class ExtraBusinessSystems(models.Model):
    title = models.CharField(max_length=30, blank=True, null=True)
    item_id = models.CharField(max_length=255)
    category = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'extra_business_systems'


class ExtraFields(models.Model):
    title = models.CharField(max_length=30, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'extra_fields'


class ExtraFieldsCustomers(models.Model):
    field_id = models.PositiveIntegerField()
    customers_id = models.PositiveIntegerField()
    val = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'extra_fields_customers'


class ExtraRCustomers(models.Model):
    field_id = models.PositiveIntegerField()
    customers_id = models.PositiveIntegerField()
    val = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'extra_r_customers'


class ExtraRNotes(models.Model):
    id = models.AutoField(unique=True)
    customers_id = models.IntegerField(blank=True, null=True)
    date = models.CharField(max_length=255)
    item_id = models.CharField(max_length=255)
    skoeinvoice = models.IntegerField(db_column='SKOEInvoice', blank=True, null=True)  # Field name made lowercase.
    notes = models.TextField()

    class Meta:
        managed = False
        db_table = 'extra_r_notes'


class ExtraRetailSystems(models.Model):
    title = models.CharField(max_length=30, blank=True, null=True)
    item_id = models.CharField(max_length=255)
    category = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'extra_retail_systems'


class ExtraSCustomers(models.Model):
    field_id = models.PositiveIntegerField()
    customers_id = models.PositiveIntegerField()
    val = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'extra_s_customers'


class ExtraSNotes(models.Model):
    id = models.AutoField(unique=True)
    customers_id = models.IntegerField(blank=True, null=True)
    date = models.CharField(max_length=255)
    item_id = models.CharField(max_length=255)
    skoeinvoice = models.IntegerField(db_column='SKOEInvoice', blank=True, null=True)  # Field name made lowercase.
    notes = models.TextField()

    class Meta:
        managed = False
        db_table = 'extra_s_notes'


class ExtraServiceSystems(models.Model):
    title = models.CharField(max_length=30, blank=True, null=True)
    item_id = models.CharField(max_length=255)
    category = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'extra_service_systems'


class Faq(models.Model):
    name = models.CharField(max_length=100)
    email = models.CharField(max_length=50)
    category_id = models.SmallIntegerField(blank=True, null=True)
    question = models.TextField()
    answer = models.TextField(blank=True, null=True)
    display = models.CharField(max_length=1)
    date_added = models.DateTimeField()
    faq_type = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'faq'


class Files(models.Model):
    file = models.CharField(max_length=150, blank=True, null=True)
    date = models.DateTimeField()
    upload_ok = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'files'


class GeoZones(models.Model):
    geo_zone_id = models.AutoField(primary_key=True)
    geo_zone_name = models.CharField(max_length=32)
    geo_zone_description = models.CharField(max_length=255)
    last_modified = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'geo_zones'


class GlobalSec(models.Model):
    password = models.CharField(max_length=255)
    last_updated = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'global_sec'


class GroupMembers(models.Model):
    group_id = models.IntegerField()
    user_id = models.IntegerField()
    join_date = models.DateField()

    class Meta:
        managed = False
        db_table = 'group_members'


class Groups(models.Model):
    owner_id = models.IntegerField()
    name = models.CharField(max_length=200)
    create_date = models.DateField()

    class Meta:
        managed = False
        db_table = 'groups'


class ImportExportExportCardUsersProfileCustomer(models.Model):
    customer_id = models.IntegerField()
    authorizenet_profile_id = models.CharField(max_length=25)
    authorizenet_payment_profile_id = models.CharField(max_length=25)
    cc_last_4 = models.IntegerField()
    cc_exp_year = models.IntegerField()
    cc_exp_month = models.IntegerField()
    created_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'import_export__export_card_users_profile__customer'


class ImportExportExportCardUsersProfileProfessionalMember(models.Model):
    customer_id = models.CharField(max_length=20)
    authorizenet_profile_id = models.CharField(max_length=25)
    authorizenet_payment_profile_id = models.CharField(max_length=25)
    cc_last_4 = models.IntegerField()
    cc_exp_year = models.IntegerField()
    cc_exp_month = models.IntegerField()
    created_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'import_export__export_card_users_profile__professional_member'


class Invcheck(models.Model):
    invoice_no = models.IntegerField(primary_key=True)

    class Meta:
        managed = False
        db_table = 'invcheck'


class Languages(models.Model):
    languages_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=32)
    code = models.CharField(max_length=2)
    image = models.CharField(max_length=64, blank=True, null=True)
    directory = models.CharField(max_length=32, blank=True, null=True)
    sort_order = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'languages'


class LastResult(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'last_result'


class LeadList1(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_1'


class LeadList2(models.Model):
    contact_id = models.PositiveIntegerField()
    if21 = models.IntegerField(db_column='If21', blank=True, null=True)  # Field name made lowercase.
    sf22 = models.TextField(db_column='Sf22', blank=True, null=True)  # Field name made lowercase.

    class Meta:
        managed = False
        db_table = 'lead_list_2'


class LeadList3(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_3'


class LeadList4(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_4'


class LeadList5(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_5'


class LeadList6(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_6'


class LeadList7(models.Model):
    contact_id = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'lead_list_7'


class MailLog(models.Model):
    to = models.CharField(max_length=255)
    from_field = models.CharField(db_column='from', max_length=255, blank=True, null=True)  # Field renamed because it was a Python reserved word.
    subject = models.CharField(max_length=255)
    message = models.TextField()
    header = models.TextField(blank=True, null=True)
    sent_on = models.CharField(max_length=20, blank=True, null=True)
    sent_by = models.CharField(max_length=255)
    member_id = models.IntegerField()
    amico_id = models.CharField(max_length=255)
    queue_code = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mail_log'


class MailLogChunk(models.Model):
    chunk_id = models.AutoField(primary_key=True)
    queue_mail_id = models.IntegerField()
    queue_code = models.CharField(max_length=50)
    mail_log_id = models.IntegerField()
    status = models.IntegerField()
    created = models.DateTimeField()
    updated = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'mail_log_chunk'


class MailLogQueueCompleteEmail(models.Model):
    queue_mail_id = models.AutoField(primary_key=True)
    queue_code = models.CharField(max_length=50)
    sender_id = models.CharField(max_length=1000)
    sender_email = models.TextField()
    subject = models.TextField()
    status = models.IntegerField()
    created = models.DateTimeField()
    updated = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'mail_log_queue_complete_email'


class MailchimpBouncedEmails(models.Model):
    campaign = models.ForeignKey('MailchimpBouncedEmailsCampaigns', models.DO_NOTHING)
    email = models.CharField(max_length=255)
    firstname = models.CharField(max_length=100, blank=True, null=True)
    lastname = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mailchimp_bounced_emails'


class MailchimpBouncedEmailsCampaigns(models.Model):
    name = models.CharField(max_length=255)
    created = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'mailchimp_bounced_emails_campaigns'


class MailchimpUsers(models.Model):
    user_type = models.CharField(max_length=255)
    email = models.CharField(max_length=255)
    first_name = models.CharField(max_length=255, blank=True, null=True)
    last_name = models.CharField(max_length=255, blank=True, null=True)
    website_id = models.CharField(max_length=255, blank=True, null=True)
    nickname = models.CharField(max_length=255, blank=True, null=True)
    is_media_artist = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'mailchimp_users'


class Manufacturers(models.Model):
    manufacturers_id = models.AutoField(primary_key=True)
    manufacturers_name = models.CharField(max_length=32)
    manufacturers_image = models.CharField(max_length=64, blank=True, null=True)
    date_added = models.DateTimeField(blank=True, null=True)
    last_modified = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'manufacturers'


class ManufacturersInfo(models.Model):
    manufacturers_id = models.IntegerField(primary_key=True)
    manufacturers_url = models.CharField(max_length=255)
    url_clicked = models.IntegerField()
    date_last_click = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'manufacturers_info'


class MemberCustomers(models.Model):
    member_id = models.IntegerField()
    members_customer_id = models.AutoField(primary_key=True)
    first_name = models.CharField(max_length=55)
    last_name = models.CharField(max_length=55)
    street_address = models.CharField(max_length=100)
    city = models.CharField(max_length=55)
    state = models.CharField(max_length=2)
    zip = models.CharField(max_length=5)
    email = models.CharField(max_length=100)
    phone = models.CharField(max_length=13)
    dob = models.DateField()

    class Meta:
        managed = False
        db_table = 'member_customers'


class MobileOperators(models.Model):
    operator = models.CharField(max_length=256)
    operator_address = models.CharField(max_length=256)

    class Meta:
        managed = False
        db_table = 'mobile_operators'


class Need(models.Model):
    need = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'need'


class NewStwData(models.Model):
    type = models.CharField(max_length=3)
    report_id = models.IntegerField()
    member_id = models.CharField(max_length=20)
    src_member_id = models.CharField(max_length=20)
    invoice_id = models.IntegerField()
    invoice_date = models.DateField()
    level = models.IntegerField()
    member_path = models.CharField(max_length=150)
    amount = models.DecimalField(max_digits=65, decimal_places=2)
    commissionable = models.DecimalField(max_digits=65, decimal_places=2)
    commissioned = models.DecimalField(max_digits=65, decimal_places=2)
    percentage = models.DecimalField(max_digits=65, decimal_places=3)

    class Meta:
        managed = False
        db_table = 'new_stw_data'


class Newsletters(models.Model):
    newsletters_id = models.AutoField(primary_key=True)
    title = models.CharField(max_length=255)
    content = models.TextField()
    module = models.CharField(max_length=255)
    date_added = models.DateTimeField()
    date_sent = models.DateTimeField(blank=True, null=True)
    status = models.IntegerField(blank=True, null=True)
    locked = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'newsletters'


class NewslettersArticles(models.Model):
    newsletter_id = models.PositiveIntegerField()
    article_id = models.PositiveIntegerField()
    position = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'newsletters_articles'


class NewslettersMails(models.Model):
    date_created = models.DateTimeField(blank=True, null=True)
    archived = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'newsletters_mails'


class Notice(models.Model):
    notice_id = models.AutoField(primary_key=True)
    notice_head = models.CharField(max_length=100, blank=True, null=True)
    notice_body = models.TextField(blank=True, null=True)
    notice_type = models.CharField(max_length=10, blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'notice'


class PastDesignations(models.Model):
    report_id = models.IntegerField(primary_key=True)
    member_id = models.CharField(max_length=255)
    int_designation_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'past_designations'
        unique_together = (('report_id', 'member_id'),)


class PuntacanaIncentives(models.Model):
    report_id = models.AutoField(primary_key=True)
    member_id = models.IntegerField()
    total_sale = models.FloatField()
    incentive_type = models.CharField(max_length=100)
    incentive_percentage = models.IntegerField()
    created = models.DateTimeField()
    updated = models.DateTimeField()
    updated_by_cron = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'puntacana_incentives'
        unique_together = (('member_id', 'incentive_type'),)


class ReasonDesc(models.Model):
    id = models.SmallAutoField(primary_key=True)
    name = models.CharField(max_length=100, blank=True, null=True)
    type = models.CharField(max_length=4, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'reason_desc'


class Reports(models.Model):
    reports_id = models.AutoField(primary_key=True)
    reports_title = models.CharField(max_length=255)
    reports_desc = models.TextField()
    reports_url = models.CharField(max_length=255, blank=True, null=True)
    reports_status = models.IntegerField()
    reports_file = models.CharField(max_length=255, blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'reports'


class Reviews(models.Model):
    reviews_id = models.AutoField(primary_key=True)
    products_id = models.IntegerField()
    customers_id = models.IntegerField(blank=True, null=True)
    customers_name = models.CharField(max_length=64)
    reviews_rating = models.IntegerField(blank=True, null=True)
    date_added = models.DateTimeField(blank=True, null=True)
    last_modified = models.DateTimeField(blank=True, null=True)
    reviews_read = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'reviews'


class ReviewsDescription(models.Model):
    reviews_id = models.IntegerField(primary_key=True)
    reviews_text = models.TextField()

    class Meta:
        managed = False
        db_table = 'reviews_description'


class SalonInquire(models.Model):
    date = models.DateTimeField(blank=True, null=True)
    user_name = models.CharField(db_column='User_name', max_length=50, blank=True, null=True)  # Field name made lowercase.
    business = models.CharField(max_length=50, blank=True, null=True)
    address = models.CharField(max_length=100, blank=True, null=True)
    city = models.CharField(max_length=50, blank=True, null=True)
    province = models.CharField(max_length=50, blank=True, null=True)
    postal_code = models.CharField(max_length=50, blank=True, null=True)
    dayime_phone = models.CharField(max_length=50, blank=True, null=True)
    other_phone = models.CharField(max_length=50, blank=True, null=True)
    fax_number = models.CharField(max_length=50, blank=True, null=True)
    user_email = models.CharField(db_column='User_email', max_length=50, blank=True, null=True)  # Field name made lowercase.
    company = models.CharField(max_length=50, blank=True, null=True)
    position = models.CharField(max_length=50, blank=True, null=True)
    services = models.CharField(max_length=50, blank=True, null=True)
    have_cruise = models.CharField(max_length=50, blank=True, null=True)
    who_may_be_joining_you = models.CharField(db_column='Who_may_be_joining_you', max_length=50, blank=True, null=True)  # Field name made lowercase.
    working_at_location = models.CharField(max_length=50, blank=True, null=True)
    date_mailed = models.DateField(blank=True, null=True)
    date_followup = models.DateField(blank=True, null=True)
    call_completed = models.CharField(max_length=100, blank=True, null=True)
    notes = models.CharField(max_length=200, blank=True, null=True)
    source = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'salon_inquire'


class Specials(models.Model):
    specials_id = models.AutoField(primary_key=True)
    products_id = models.IntegerField()
    specials_new_products_price = models.DecimalField(max_digits=8, decimal_places=2)
    specials_date_added = models.DateTimeField(blank=True, null=True)
    specials_last_modified = models.DateTimeField(blank=True, null=True)
    expires_date = models.DateTimeField(blank=True, null=True)
    date_status_change = models.DateTimeField(blank=True, null=True)
    status = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'specials'


class State(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'state'


class Status(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'status'


class StwData(models.Model):
    type = models.CharField(max_length=3)
    report_id = models.IntegerField()
    member_id = models.CharField(max_length=20)
    src_member_id = models.CharField(max_length=20)
    invoice_id = models.IntegerField()
    invoice_date = models.DateField()
    level = models.IntegerField()
    member_path = models.CharField(max_length=150)
    amount = models.DecimalField(max_digits=65, decimal_places=2)
    commissionable = models.DecimalField(max_digits=65, decimal_places=2)
    commissioned = models.DecimalField(max_digits=65, decimal_places=2)
    percentage = models.DecimalField(max_digits=65, decimal_places=3)

    class Meta:
        managed = False
        db_table = 'stw_data'


class StwInquire(models.Model):
    date = models.DateTimeField()
    firstname = models.CharField(max_length=50, blank=True, null=True)
    lastname = models.CharField(max_length=50, blank=True, null=True)
    phone = models.CharField(max_length=15)
    email = models.CharField(max_length=50, blank=True, null=True)
    street = models.CharField(max_length=150, blank=True, null=True)
    street2 = models.CharField(max_length=150)
    city = models.CharField(max_length=50, blank=True, null=True)
    state = models.CharField(max_length=50, blank=True, null=True)
    zip = models.CharField(max_length=12, blank=True, null=True)
    country = models.CharField(max_length=2, blank=True, null=True)
    comment = models.TextField(blank=True, null=True)
    howdidyouhear = models.CharField(max_length=255, blank=True, null=True)
    kindofperson = models.CharField(max_length=150, blank=True, null=True)
    requestreferrer = models.CharField(max_length=150, blank=True, null=True)
    email_sent = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'stw_inquire'


class StwReports(models.Model):
    report_id = models.AutoField(primary_key=True)
    report_time = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'stw_reports'


class Task(models.Model):
    user_id = models.IntegerField(blank=True, null=True)
    contact_id = models.IntegerField(blank=True, null=True)
    type = models.CharField(max_length=20, blank=True, null=True)
    priority = models.CharField(max_length=20, blank=True, null=True)
    timeless = models.CharField(max_length=8, blank=True, null=True)
    task_date = models.DateTimeField(blank=True, null=True)
    duration = models.CharField(max_length=20, blank=True, null=True)
    task_regarding = models.CharField(max_length=60, blank=True, null=True)
    set_alarm = models.IntegerField(blank=True, null=True)
    leadtime = models.IntegerField(blank=True, null=True)
    detail = models.TextField(blank=True, null=True)
    time_type = models.CharField(max_length=3)
    js_popup = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'task'


class TaskRegarding(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'task_regarding'


class TaxClass(models.Model):
    tax_class_id = models.AutoField(primary_key=True)
    tax_class_title = models.CharField(max_length=32)
    tax_class_description = models.CharField(max_length=255)
    last_modified = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'tax_class'


class TaxRates(models.Model):
    tax_rates_id = models.AutoField(primary_key=True)
    tax_zone_id = models.IntegerField()
    tax_class_id = models.IntegerField()
    tax_priority = models.IntegerField(blank=True, null=True)
    tax_rate = models.DecimalField(max_digits=7, decimal_places=4)
    tax_description = models.CharField(max_length=255)
    last_modified = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'tax_rates'


class TblAdmin(models.Model):
    int_admin_id = models.AutoField(primary_key=True)
    str_first_name = models.CharField(max_length=50, blank=True, null=True)
    str_last_name = models.CharField(max_length=50, blank=True, null=True)
    str_username = models.CharField(max_length=50, blank=True, null=True)
    str_password = models.CharField(max_length=50, blank=True, null=True)
    str_email = models.CharField(max_length=50, blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_admin'


class TblAdminEmail(models.Model):
    int_admin_email_id = models.AutoField(primary_key=True)
    str_admin_email = models.CharField(max_length=50, blank=True, null=True)
    str_admin_email1 = models.CharField(max_length=50, blank=True, null=True)
    str_admin_email2 = models.CharField(max_length=50, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_admin_email'


class TblAdminLogs(models.Model):
    int_admin_id = models.CharField(max_length=25)
    str_username = models.CharField(max_length=50, blank=True, null=True)
    dt = models.DateTimeField(blank=True, null=True)
    user_type = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'tbl_admin_logs'


class TblAnnotateNote(models.Model):
    int_annotate_note_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    int_member_list = models.IntegerField(blank=True, null=True)
    bit_credit_card = models.IntegerField(blank=True, null=True)
    int_status_id = models.IntegerField(blank=True, null=True)
    str_legal_issue1 = models.CharField(max_length=20, blank=True, null=True)
    str_legal_issue2 = models.CharField(max_length=20, blank=True, null=True)
    dtt_legal = models.DateField(blank=True, null=True)
    str_client_comments = models.TextField(blank=True, null=True)
    str_notes = models.TextField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_annotate_note'


class TblCalls(models.Model):
    calldate = models.CharField(max_length=30)
    calldestination = models.CharField(max_length=20)
    billabletime = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'tbl_calls'


class TblCommisionRule(models.Model):
    int_commision_rule_id = models.AutoField(primary_key=True)
    str_commision_rule = models.CharField(max_length=50, blank=True, null=True)
    bit_commisionable = models.IntegerField(blank=True, null=True)
    bit_percentage = models.IntegerField(blank=True, null=True)
    int_value = models.IntegerField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_commision_rule'


class TblCommisionSalesHistory(models.Model):
    int_commision_sales_history_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    dtt_calculate = models.DateField(blank=True, null=True)
    int_commision = models.DecimalField(max_digits=8, decimal_places=2, blank=True, null=True)
    int_sales = models.DecimalField(max_digits=8, decimal_places=2, blank=True, null=True)
    int_month = models.IntegerField(blank=True, null=True)
    int_year = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_commision_sales_history'


class TblCustomerNotes(models.Model):
    int_customer_note_id = models.AutoField(primary_key=True)
    int_customer_id = models.IntegerField(blank=True, null=True)
    str_comments = models.TextField(blank=True, null=True)
    dtt_notes = models.DateField(blank=True, null=True)
    str_notes = models.TextField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_customer_notes'


class TblDesignation(models.Model):
    int_designation_id = models.AutoField(primary_key=True)
    str_designation = models.CharField(max_length=50, blank=True, null=True)
    designation_percentage = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'tbl_designation'


class TblDesignationBu(models.Model):
    int_designation_id = models.AutoField(primary_key=True)
    str_designation = models.CharField(max_length=50, blank=True, null=True)
    designation_percentage = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'tbl_designation_bu'


class TblMemberContactList(models.Model):
    int_member_contact_list_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    str_member_contact_list = models.TextField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_member_contact_list'


class TblMemberEc(models.Model):
    amico_id = models.CharField(max_length=255)
    ec_id = models.IntegerField()
    event = models.CharField(max_length=10)
    timestamp = models.CharField(max_length=20)

    class Meta:
        managed = False
        db_table = 'tbl_member_ec'


class TblMemberRenamed(models.Model):
    int_member_id = models.IntegerField(blank=True, null=True)
    old_nickname = models.CharField(max_length=255, blank=True, null=True)
    new_nickname = models.CharField(max_length=255, blank=True, null=True)
    mtype = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_member_renamed'


class TblMemberSubscription(models.Model):
    ref_member_id = models.IntegerField()
    sub_member_id = models.IntegerField()
    subscribed = models.IntegerField()
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'tbl_member_subscription'


class TblMemberToEc(models.Model):
    amico_id = models.CharField(max_length=15)
    ec_id = models.CharField(max_length=10, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_member_to_ec'


class TblMlmErrors(models.Model):
    mlm_id = models.PositiveIntegerField()
    date1 = models.DateTimeField(blank=True, null=True)
    category = models.CharField(max_length=20, blank=True, null=True)
    type = models.CharField(max_length=100, blank=True, null=True)
    notes = models.TextField(blank=True, null=True)
    status = models.CharField(max_length=20, blank=True, null=True)
    date2 = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_mlm_errors'


class TblMlmErrorsTypes(models.Model):
    eid = models.AutoField(primary_key=True)
    title = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'tbl_mlm_errors_types'


class TblNews(models.Model):
    int_news_id = models.AutoField(primary_key=True)
    str_title = models.CharField(max_length=50, blank=True, null=True)
    str_news = models.TextField(blank=True, null=True)
    str_date = models.DateField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_news'


class TblNewsletter(models.Model):
    int_newsletter_id = models.AutoField(primary_key=True)
    str_subject = models.CharField(max_length=50, blank=True, null=True)
    str_newsletter = models.TextField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)
    targetaudience = models.CharField(db_column='TargetAudience', max_length=9, blank=True, null=True)  # Field name made lowercase.
    int_days = models.IntegerField(blank=True, null=True)
    sms = models.IntegerField()
    sms_msg = models.CharField(max_length=100)

    class Meta:
        managed = False
        db_table = 'tbl_newsletter'


class TblNewsletterNew(models.Model):
    title = models.CharField(max_length=50, blank=True, null=True)
    body = models.TextField(blank=True, null=True)
    status = models.IntegerField(blank=True, null=True)
    img = models.CharField(max_length=250)
    img1 = models.CharField(max_length=250, blank=True, null=True)
    img2 = models.CharField(max_length=250)
    img3 = models.CharField(max_length=250)
    img4 = models.CharField(max_length=250)
    short_descr = models.TextField(blank=True, null=True)
    dt = models.DateField()

    class Meta:
        managed = False
        db_table = 'tbl_newsletter_new'


class TblNoteStatus(models.Model):
    int_note_status_id = models.AutoField(primary_key=True)
    str_note_status = models.CharField(max_length=20, blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_note_status'


class TblProductLevel(models.Model):
    int_product_level_id = models.AutoField(primary_key=True)
    int_product_id = models.IntegerField(blank=True, null=True)
    int_commision_level1 = models.IntegerField(blank=True, null=True)
    int_commision_level2 = models.IntegerField(blank=True, null=True)
    int_commision_level3 = models.IntegerField(blank=True, null=True)
    int_commision_level4 = models.IntegerField(blank=True, null=True)
    int_commision_level5 = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_product_level'


class TblSalesrecord(models.Model):
    int_salesrecord_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    dtt_record = models.DateField(blank=True, null=True)
    int_salesrecord = models.IntegerField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)
    description = models.CharField(max_length=255)
    reward = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'tbl_salesrecord'


class TblScheduleList(models.Model):
    int_schedule_list_id = models.AutoField(primary_key=True)
    int_member_id = models.IntegerField(blank=True, null=True)
    dtt_schedule = models.DateField(blank=True, null=True)
    tme_schedule = models.TimeField(blank=True, null=True)
    str_schedule_meridian = models.CharField(max_length=5, blank=True, null=True)
    str_contact = models.CharField(max_length=20, blank=True, null=True)
    str_reason = models.TextField(blank=True, null=True)
    dtt_callback = models.DateField(blank=True, null=True)
    tme_callback = models.TimeField(blank=True, null=True)
    str_callback_meridian = models.CharField(max_length=5, blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)
    customers_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'tbl_schedule_list'


class TempTable(models.Model):
    model = models.CharField(max_length=255, blank=True, null=True)
    member = models.DecimalField(max_digits=8, decimal_places=2, blank=True, null=True)
    a = models.DecimalField(max_digits=8, decimal_places=2, blank=True, null=True)
    b = models.DecimalField(max_digits=8, decimal_places=2, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'temp_table'


class Title(models.Model):
    name = models.CharField(max_length=60, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'title'


class ToolLinks(models.Model):
    tool_id = models.AutoField(primary_key=True)
    tool_title = models.CharField(max_length=255)
    tool_link = models.CharField(max_length=255)
    tool_description = models.TextField()

    class Meta:
        managed = False
        db_table = 'tool_links'


class UploadFiles(models.Model):
    file_id = models.AutoField(primary_key=True)
    file_folder_id = models.IntegerField()
    file_name = models.CharField(max_length=255)
    file_size = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'upload_files'


class UploadFolders(models.Model):
    folder_id = models.AutoField(primary_key=True)
    folder_name = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'upload_folders'


class Users(models.Model):
    username = models.CharField(max_length=20)
    pass_field = models.CharField(db_column='pass', max_length=20)  # Field renamed because it was a Python reserved word.
    fname = models.CharField(max_length=40)
    lname = models.CharField(max_length=40)
    company = models.CharField(max_length=60)
    title = models.CharField(max_length=40)
    department = models.CharField(max_length=40)
    city = models.CharField(max_length=100)
    state = models.CharField(max_length=2)
    country = models.CharField(max_length=40)
    phone = models.CharField(max_length=40)
    fax = models.CharField(max_length=40)
    email = models.CharField(max_length=40)
    www = models.CharField(max_length=100)
    insert_date = models.DateField(blank=True, null=True)
    su = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'users'


class VideoFolders(models.Model):
    folder_id = models.AutoField(primary_key=True)
    folder_name = models.CharField(max_length=1000)

    class Meta:
        managed = False
        db_table = 'video_folders'


class VideoLinks(models.Model):
    video_id = models.AutoField(primary_key=True)
    folder_id = models.IntegerField()
    video_title = models.CharField(max_length=255)
    video_link = models.CharField(max_length=255)
    video_description = models.TextField()

    class Meta:
        managed = False
        db_table = 'video_links'


class Visits(models.Model):
    form_name = models.CharField(unique=True, max_length=64)
    visits = models.PositiveIntegerField()
    form = models.PositiveIntegerField()

    class Meta:
        managed = False
        db_table = 'visits'


class WhosOnline(models.Model):
    customer_id = models.IntegerField(blank=True, null=True)
    full_name = models.CharField(max_length=64)
    session_id = models.CharField(max_length=128)
    ip_address = models.CharField(max_length=15)
    time_entry = models.CharField(max_length=14)
    time_last_click = models.CharField(max_length=14)
    last_page_url = models.CharField(max_length=64)

    class Meta:
        managed = False
        db_table = 'whos_online'


class WpComments(models.Model):
    comment_id = models.BigAutoField(db_column='comment_ID', primary_key=True)  # Field name made lowercase.
    comment_post_id = models.IntegerField(db_column='comment_post_ID')  # Field name made lowercase.
    comment_author = models.TextField()
    comment_author_email = models.CharField(max_length=100)
    comment_author_url = models.CharField(max_length=200)
    comment_author_ip = models.CharField(db_column='comment_author_IP', max_length=100)  # Field name made lowercase.
    comment_date = models.DateTimeField()
    comment_date_gmt = models.DateTimeField()
    comment_content = models.TextField()
    comment_karma = models.IntegerField()
    comment_approved = models.CharField(max_length=20)
    comment_agent = models.CharField(max_length=255)
    comment_type = models.CharField(max_length=20)
    comment_parent = models.BigIntegerField()
    user_id = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'wp_comments'


class WpLinks(models.Model):
    link_id = models.BigAutoField(primary_key=True)
    link_url = models.CharField(max_length=255)
    link_name = models.CharField(max_length=255)
    link_image = models.CharField(max_length=255)
    link_target = models.CharField(max_length=25)
    link_category = models.BigIntegerField()
    link_description = models.CharField(max_length=255)
    link_visible = models.CharField(max_length=20)
    link_owner = models.IntegerField()
    link_rating = models.IntegerField()
    link_updated = models.DateTimeField()
    link_rel = models.CharField(max_length=255)
    link_notes = models.TextField()
    link_rss = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'wp_links'


class WpOptions(models.Model):
    option_id = models.BigAutoField(primary_key=True)
    blog_id = models.IntegerField()
    option_name = models.CharField(max_length=64)
    option_value = models.TextField()
    autoload = models.CharField(max_length=20)

    class Meta:
        managed = False
        db_table = 'wp_options'
        unique_together = (('option_id', 'blog_id', 'option_name'),)


class WpPostmeta(models.Model):
    meta_id = models.BigAutoField(primary_key=True)
    post_id = models.BigIntegerField()
    meta_key = models.CharField(max_length=255, blank=True, null=True)
    meta_value = models.TextField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'wp_postmeta'


class WpPosts(models.Model):
    id = models.BigAutoField(db_column='ID', primary_key=True)  # Field name made lowercase.
    post_author = models.BigIntegerField()
    post_date = models.DateTimeField()
    post_date_gmt = models.DateTimeField()
    post_content = models.TextField()
    post_title = models.TextField()
    post_category = models.IntegerField()
    post_excerpt = models.TextField()
    post_status = models.CharField(max_length=20)
    comment_status = models.CharField(max_length=20)
    ping_status = models.CharField(max_length=20)
    post_password = models.CharField(max_length=20)
    post_name = models.CharField(max_length=200)
    to_ping = models.TextField()
    pinged = models.TextField()
    post_modified = models.DateTimeField()
    post_modified_gmt = models.DateTimeField()
    post_content_filtered = models.TextField()
    post_parent = models.BigIntegerField()
    guid = models.CharField(max_length=255)
    menu_order = models.IntegerField()
    post_type = models.CharField(max_length=20)
    post_mime_type = models.CharField(max_length=100)
    comment_count = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'wp_posts'


class WpTermRelationships(models.Model):
    object_id = models.BigIntegerField(primary_key=True)
    term_taxonomy_id = models.BigIntegerField()
    term_order = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'wp_term_relationships'
        unique_together = (('object_id', 'term_taxonomy_id'),)


class WpTermTaxonomy(models.Model):
    term_taxonomy_id = models.BigAutoField(primary_key=True)
    term_id = models.BigIntegerField()
    taxonomy = models.CharField(max_length=32)
    description = models.TextField()
    parent = models.BigIntegerField()
    count = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'wp_term_taxonomy'
        unique_together = (('term_id', 'taxonomy'),)


class WpTerms(models.Model):
    term_id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=55)
    slug = models.CharField(unique=True, max_length=200)
    term_group = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'wp_terms'


class WpUsermeta(models.Model):
    umeta_id = models.BigAutoField(primary_key=True)
    user_id = models.BigIntegerField()
    meta_key = models.CharField(max_length=255, blank=True, null=True)
    meta_value = models.TextField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'wp_usermeta'


class WpUsers(models.Model):
    id = models.BigAutoField(db_column='ID', primary_key=True)  # Field name made lowercase.
    user_login = models.CharField(max_length=60)
    user_pass = models.CharField(max_length=64)
    user_nicename = models.CharField(max_length=50)
    user_email = models.CharField(max_length=100)
    user_url = models.CharField(max_length=100)
    user_registered = models.DateTimeField()
    user_activation_key = models.CharField(max_length=60)
    user_status = models.IntegerField()
    display_name = models.CharField(max_length=250)

    class Meta:
        managed = False
        db_table = 'wp_users'


class ZonesToGeoZones(models.Model):
    association_id = models.AutoField(primary_key=True)
    zone_country_id = models.IntegerField()
    zone_id = models.IntegerField(blank=True, null=True)
    geo_zone_id = models.IntegerField(blank=True, null=True)
    last_modified = models.DateTimeField(blank=True, null=True)
    date_added = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'zones_to_geo_zones'
