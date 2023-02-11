from django.db import models


class BwInvoiceLineItems(models.Model):
    id = models.CharField(db_column='ID', primary_key=True, max_length=100)  # Field name made lowercase.
    description = models.TextField(db_column='Description')  # Field name made lowercase.
    shipqty = models.IntegerField(db_column='ShipQty')  # Field name made lowercase.
    unitprice = models.FloatField(db_column='UnitPrice')  # Field name made lowercase.
    fkentity = models.IntegerField(db_column='FKEntity')  # Field name made lowercase.

    class Meta:
        managed = False
        db_table = 'bw_invoice_line_items'
        unique_together = (('id', 'description', 'shipqty', 'unitprice', 'fkentity'),)


class BwInvoices(models.Model):
    invoiceno = models.IntegerField(db_column='InvoiceNo', primary_key=True)  # Field name made lowercase.
    salesrepidno = models.IntegerField(db_column='SalesRepIDNo')  # Field name made lowercase.
    invoicedate = models.DateField(db_column='InvoiceDate')  # Field name made lowercase.
    orderdate = models.DateField(db_column='OrderDate')  # Field name made lowercase.
    orderno = models.IntegerField(db_column='OrderNo')  # Field name made lowercase.
    id = models.CharField(db_column='ID', max_length=10)  # Field name made lowercase.
    name = models.CharField(db_column='Name', max_length=255)  # Field name made lowercase.
    skoeinvoice = models.IntegerField(db_column='SKOEInvoice')  # Field name made lowercase.

    class Meta:
        managed = False
        db_table = 'bw_invoices'