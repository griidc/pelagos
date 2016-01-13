#!/bin/sh

PelagosDBScript[0]='01_person/01_make_person_table.sql'
PelagosDBScript[1]='01_person/02_make_email_table.sql'
PelagosDBScript[2]='01_person/03_make_email2person_table.sql'
PelagosDBScript[3]='01_person/04_make_person_history_table.sql'
PelagosDBScript[4]='01_person/05_make_person_view.sql'

PelagosDBScript[5]='02_data_repository/01_make_data_repository_table.sql'
PelagosDBScript[6]='02_data_repository/02_make_email2data_repository_table.sql'
PelagosDBScript[7]='02_data_repository/03_make_data_repository_history_table.sql'
PelagosDBScript[8]='02_data_repository/04_make_data_repository_view.sql'

PelagosDBScript[9]='03_funding_organization/01_make_funding_organization_table.sql'
PelagosDBScript[10]='03_funding_organization/02_make_email2funding_organization.sql'
PelagosDBScript[11]='03_funding_organization/03_make_funding_organization_history_table.sql'
PelagosDBScript[12]='03_funding_organization/04_make_funding_organization_view.sql'

PelagosDBScript[13]='04_funding_cycle/01_make_funding_cycle_table.sql'
PelagosDBScript[14]='04_funding_cycle/02_make_funding_cycle_history_table.sql'
PelagosDBScript[15]='04_funding_cycle/03_make_funding_cycle_view.sql'

PelagosDBScript[16]='05_research_group/01_make_research_group_table.sql'
PelagosDBScript[17]='05_research_group/02_make_email2research_group.sql'
PelagosDBScript[18]='05_research_group/03_make_research_group_history_table.sql'
PelagosDBScript[19]='05_research_group/04_make_research_group_view.sql'

PelagosDBScript[20]='06_research_group_role/01_make_research_group_role_table.sql'
PelagosDBScript[21]='06_research_group_role/02_make_research_group_role_view.sql'

PelagosDBScript[22]='07_person2research_group2role/01_make_person2research_group2role_table.sql'
PelagosDBScript[23]='07_person2research_group2role/02_make_person2research_group2role_view.sql'

PelagosDBScript[24]='08_funding_organization_role/01_make_funding_organization_role_table.sql'
PelagosDBScript[25]='08_funding_organization_role/02_make_funding_organization_role_view.sql'

PelagosDBScript[26]='09_person2funding_organization2role/01_make_person2funding_organization2role_table.sql'
PelagosDBScript[27]='09_person2funding_organization2role/02_make_person2funding_organization2role_view.sql'

# 10 was intentionally left blank as it was data_repository at one point, but'
# had to be moved up in the order due to dependencies.'

PelagosDBScript[28]='11_data_repository_role/01_make_data_repository_role_table.sql'
PelagosDBScript[29]='11_data_repository_role/02_make_data_repository_role_view.sql'

PelagosDBScript[30]='12_person2data_repository2role/01_make_person2data_repository2role_table.sql'
PelagosDBScript[31]='12_person2data_repository2role/02_make_person2data_repository2role_view.sql'

PelagosDBScript[32]='13_account/01_make_account_table.sql'
PelagosDBScript[33]='13_account/02_make_account_view.sql'

PelagosDBScript[34]='14_person_token/01_make_person_token_table.sql'
PelagosDBScript[35]='14_person_token/02_make_person_token_view.sql'

for sql in "${PelagosDBScript[@]}"
do
    echo "${sql}"
    psql -U postgres gomri < "${sql}"
    echo
done
