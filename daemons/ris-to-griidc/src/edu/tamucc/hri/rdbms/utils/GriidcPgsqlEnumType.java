package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Vector;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.support.MiscUtils;

public class GriidcPgsqlEnumType {

	public static final String TelephoneTypeName = "telephone_type";
	public static final String AccessStatus = "access_status";
	public static final String EthicalIssues = "ethical_issues";
	public static final String FileDlStatus = "file_dl_status";
	public static final String MediaCreator = "metadata_creator";
	public static final String MetadataStatus = "metadata_status";
	public static final String Restriction = "restriction";
	public static final String XferProtocol = "xfer_protocol";

	public static final String[] PgGriidcTypeName = { TelephoneTypeName,
			AccessStatus, EthicalIssues, FileDlStatus, MediaCreator,
			MetadataStatus, Restriction, XferProtocol };

	public String[] getGriidcTelephoneTypeEnumValues()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		return this.getGriidcPgEnum(TelephoneTypeName);
	}

	public String[] getAccessStatus() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(AccessStatus);
	}

	public String[] getEthicalIssues() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(EthicalIssues);
	}

	public String[] getFileDlStatus() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(FileDlStatus);
	}

	public String[] getMediaCreator() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(MediaCreator);
	}

	public String[] getMetadataStatus() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(MetadataStatus);
	}

	public String[] getRestriction() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(Restriction);
	}

	public String[] getXferProtocol() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		return this.getGriidcPgEnum(XferProtocol);
	}

	public String[] getGriidcPgEnum(String typeName)
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		String query = "SELECT unnest(enum_range(NULL::"
				+ RdbmsConnection.wrapInDoubleQuotes(typeName) + "))";
		ResultSet rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
				.executeQueryResultSet(query);
		Vector<String> v = new Vector<String>();
		String s = null;
		while (rset.next()) {
			s = rset.getString(1);
			v.add(s);
		}
		String[] tArray = new String[v.size()];
		tArray = v.toArray(tArray);
		return tArray;

	}

	

	public GriidcPgsqlEnumType() {
		// TODO Auto-generated constructor stub
	}

	public static final String[] tableNames = { "Institution-Telephone" };

	public static void main(String[] args) {
		GriidcPgsqlEnumType griidcTypes = new GriidcPgsqlEnumType();
		try {
			for (String typeName : griidcTypes.PgGriidcTypeName) {
				String[] t = griidcTypes.getGriidcPgEnum(typeName);
				System.out.println("\n" + typeName + " type values");
				for (String s2 : t)
					System.out.println(s2);
			}

		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		
	}

}
