from django.db import models


class Countries(models.Model):
    countries_id = models.AutoField(primary_key=True)
    countries_name = models.CharField(max_length=64)
    countries_iso_code_2 = models.CharField(max_length=2)
    countries_iso_code_3 = models.CharField(max_length=3)
    address_format_id = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'countries'


class Zones(models.Model):
    zone_id = models.AutoField(primary_key=True)
    # zone_country_id = models.IntegerField()
    zone_country = models.ForeignKey(Countries, on_delete=models.CASCADE, null=True)
    zone_code = models.CharField(max_length=32)
    zone_name = models.CharField(max_length=32)

    class Meta:
        managed = False
        db_table = 'zones'


class Zipdata(models.Model):
    zipcode = models.CharField(primary_key=True, max_length=5)
    lat = models.FloatField(blank=True, null=True)
    lon = models.FloatField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'zipdata'
